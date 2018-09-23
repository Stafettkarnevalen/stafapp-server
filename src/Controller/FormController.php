<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 09/09/2017
 * Time: 1.07
 */

namespace App\Controller;

use App\Controller\Interfaces\ModalEventController;
use App\Entity\Forms\Form;
use App\Entity\Forms\FormField;
use App\Entity\Forms\FormFieldDependency;
use App\Entity\Forms\FormReport;
use App\Entity\Forms\FormSubmission;
use App\Form\FormBuilder\ClientFormType;
use App\Form\FormBuilder\EditType;
use App\Form\FormBuilder\FieldType;
use App\Repository\FormFieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends Controller implements ModalEventController
{
    /**
     * Controller for building forms (admin only).
     *
     * @Route("/{_locale}/admin/forms/list", name="nav.admin_forms")
     * @param Request $request
     * @return mixed
     */
    public function adminFormsAction(Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $sortKey = $request->get('sort', $session->get('admin_forms_sort_key', 'title'));
        $order = $request->get('order', $session->get('admin_forms_sort_order', 'ASC'));

        $orders = [];
        foreach (['title', 'from', 'until',] as $key) {
            $orders[$key] = ($sortKey == $key ? ($order == 'ASC' ? 'DESC' : 'ASC') : $order);
        }
        $session->set('admin_forms_sort_key', $sortKey);
        $session->set('admin_forms_sort_order', $order);

        $forms = $em->getRepository(Form::class)->findBy([], [$sortKey => $order]);

        return $this->render('admin/form/forms.html.twig', [
            'forms' => $forms,
            'orders' => $orders,
            'order' => $order,
            'sort' => $sortKey,
        ]);
    }

    /**
     * Controller for deleting a form (admin only).
     *
     * @Route("/{_locale}/admin/forms/delete/{id}",
     *     options={"expose"=true},
     *     name="nav.admin_form_delete")
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteFormAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $myForm = $em->getRepository(Form::class)->find($id);

        $fb = $this->createFormBuilder([], [
            'attr' => ['action' => $request->getPathInfo()]
        ]);
        $fb
            ->add('yes', SubmitType::class, ['left_icon' => 'fa-trash', 'right_icon' => 'fa-check', 'attr' => ['class' => 'btn-danger'], 'label' => 'label.yes']);

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $name = $myForm->getTitle();

            $em->remove($myForm);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.user.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 1], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_forms');
        }

        return $this->render('admin/form/delete.html.twig', [
            'formEntity' => $myForm,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /**
     * Controller for building a form (admin only).
     *
     * @Route("/{_locale}/admin/forms/form/{id}", name="nav.admin_form")
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminFormAction($id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Form $myForm */
        $myForm = null;
        if ($id) {
            $myForm = $em->getRepository(Form::class)->find($id);
        } else {
            $myForm = new Form();
            $myForm->setCreatedBy($this->getUser())->setIsActive(true)->setIsMandatory(false);
        }

        $form = $this->createForm(EditType::class, $myForm, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'form'),
            'delete_path' => $this->generateUrl('nav.admin_form_delete', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $myForm->getId() ? 'updated' : 'saved';

            if ($myForm->getId())
                $em->merge($myForm);
            else
                $em->persist($myForm);

            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.form.' . $action,
                    'parameters' => ['%name%' => $myForm->getTitle()]
                ]);
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_forms');
        } else if ($form->isSubmitted() && !$form->isValid()) {
            $formView = $form->createView();

            return $this->render('admin/form/form.html.twig', [
                'fromEntity' => $myForm,
                'form' => $formView,
                'modal' => $request->isXmlHttpRequest(),
                'btns' => $id ?
                    [
                        $formView->offsetGet('close'),
                        $formView->offsetGet('delete'),
                        $formView->offsetGet('submit')
                    ] :
                    [
                        $formView->offsetGet('close'),
                        $formView->offsetGet('submit')
                    ]
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }

        $formView = $form->createView();
        return $this->render('admin/form/form.html.twig', [
            'formEntity' => $myForm,
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'btns' => $id ?
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('delete'),
                    $formView->offsetGet('submit')
                ] :
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('submit')
                ]
        ]);
    }

    /**
     * Controller for building a form field (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_fields/form_field/{form}/{id}/{source}",
     *     options={"expose" = true},
     *     name="nav.admin_form_field")
     * @param integer $form
     * @param integer $id
     * @param integer $source
     * @param Request $request
     * @return mixed
     */
    public function adminFormFieldAction($form, $id = 0, $source = 0, Request $request)
    {
        ini_set('memory_limit', '-1');

        $em = $this->getDoctrine()->getManager();
        /** @var Form $myForm */
        $myForm = $em->getRepository(Form::class)->find($form);

        /** @var FormField $myFormField */
        $myFormField = null;
        $myFormFieldDeps = new ArrayCollection();
        if ($id) {
            $myFormField = $em->getRepository(FormField::class)->find($id);
            $myFormFieldDeps = new ArrayCollection($em->getRepository(FormFieldDependency::class)->findBy(['target' => $myFormField]));
        } else {
            $myFormField = new FormField();
            $myFormField->setForm($myForm)->setOrder($myForm->getFormFields(false)->count())->setRequired(false);
            /** @var FormField $sourceEntity */
            if ($source && $sourceEntity = $em->getRepository(FormField::class)->find($source)) {
                $myFormField->fill($sourceEntity->getFields(['order', 'dependsOn', 'dependsOnMe', 'createdAt']));
                /** @var FormFieldDependency $dep */
                foreach ($sourceEntity->getDependsOn() as $dep) {
                    /** @var FormFieldDependency $myDep */
                    $myDep = $dep->cloneEntity();
                    $myDep->setTarget($myFormField);
                    $myFormField->addDependsOn($myDep);
                }
            }
        }

        $form = $this->createForm(FieldType::class, $myFormField, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'form'),
            'delete_path' => $this->generateUrl('nav.admin_form_field_delete', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $myFormField->getId() ? 'updated' : 'saved';

            if ($myFormField->getId())
                $em->merge($myFormField);
            else
                $em->persist($myFormField);

            foreach ($myFormFieldDeps as $dep) {
                if (!$myFormField->hasDependsOn($dep))
                    $em->remove($dep);
            }

            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.form_field.' . $action,
                    'parameters' => ['%name%' => $myFormField->getTitle()]
                ]);

            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_form_fields', ['form' => $myForm->getId()]);

        } else if ($form->isSubmitted() && !$form->isValid()) {
            $formView = $form->createView();
/*
            $error = 'errors: ';
            foreach ($form as $fieldName => $formField) {
                if ($errors = $formField->getErrors(true)) {
                    if ($errors->count() > 0) {
                        $error .= "[{$fieldName}]: ";
                        foreach ($formField as $fn => $ff) {
                            $errors = $ff->getErrors(true);
                            if ($errors->count() > 0) {
                                $error .= "[{$fn}]: ";
                                foreach ($ff as $fn2 => $ff2) {
                                    $errors = $ff2->getErrors(true);
                                    if ($errors->count() > 0) {
                                        $error .= "[{$fn2}]: ";
                                        foreach ($errors as $e)
                                            $error .= print_r($e->getMessage(), true);
                                    }
                                }

                            }
                        }
                    }
                }
            }
            file_put_contents('/tmp/debug2.txt', json_encode($myFormField));

*/
            return $this->render('admin/form/form_field.html.twig', [
                'formField' => $myFormField,
                'form' => $formView,
                'modal' => $request->isXmlHttpRequest(),
                'no_toolbar' => true,
                'btns' => $id ?
                    [
                        $formView->offsetGet('close'),
                        $formView->offsetGet('delete'),
                        $formView->offsetGet('submit')
                    ] :
                    [
                        $formView->offsetGet('close'),
                        $formView->offsetGet('submit')
                    ]
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }

        $formView = $form->createView();
        return $this->render('admin/form/form_field.html.twig', [
            'formField' => $myFormField,
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'no_toolbar' => true,
            'btns' => $id ?
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('delete'),
                    $formView->offsetGet('submit')
                ] :
                [
                    $formView->offsetGet('close'),
                    $formView->offsetGet('submit')
                ]
        ]);
    }

    /**
     * Controller for deleting a form field (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_fields/delete/{id}", options={"expose"=true}, name="nav.admin_form_field_delete")
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminDeleteFormFieldAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var FormField $field */
        $field = $em->getRepository(FormField::class)->find($id);
        $formEntity = $field->getForm();

        $fb = $this->createFormBuilder([], [
            'translation_domain' => 'form',
            'attr' => ['action' => $request->getPathInfo()]
        ]);
        $fb
            ->add('yes', SubmitType::class, [
                'left_icon' => 'fa-trash',
                'right_icon' => 'fa-check',
                'attr' => ['class' => 'btn-danger'],
                'label' => 'label.yes'
            ]);

        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $name = $field->getTitle();

            $em->remove($field);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.form_field.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 0], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_form_fields', ['form' => $formEntity->getId()]);
        }

        return $this->render('admin/form/delete_form_field.html.twig', [
            'field' => $field,
            'formEntity' => $formEntity,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /**
     * Controller for building a form (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_fields/list/{form}/{move}/{field}",
     *     options={"expose"=true}, name="nav.admin_form_fields")
     * @param integer $form
     * @param integer $move
     * @param integer $field
     * @param Request $request
     * @return mixed
     */
    public function adminFormFieldsAction($form, $move = 0, $field = 0, Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        /** @var Form $myForm */
        $myForm = $em->getRepository(Form::class)->find($form);

        $sortKey = $request->get('sort', $session->get('admin_form_fields_sort_key', 'order'));
        $order = $request->get('order', $session->get('admin_form_fields_sort_order', 'ASC'));

        $orders = [];
        foreach (['order', 'title', 'type'] as $key) {
            $orders[$key] = ($sortKey == $key ? ($order == 'ASC' ? 'DESC' : 'ASC') : $order);
        }
        $session->set('admin_form_fields_sort_key', $sortKey);
        $session->set('admin_form_fields_sort_order', $order);

        $myFormFields = $em->getRepository(FormField::class)->findBy(['form' => $myForm], [$sortKey => $order]);

        $form = $this->createForm(FormType::class, null, [])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'label' => 'label.close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
            ]);
        $formView = $form->createView();
        if ($move !== 0) {
            /** @var FormFieldRepository $repo */
            $repo = $em->getRepository(FormField::class);
            $field = $repo->find($field);

            try {
                $oldOrder = $field->getOrder();
                $field->setOrder($oldOrder + $move);
                if ($move > 0) {
                    $from = $oldOrder + 1;
                    $until = $oldOrder + $move;
                    $up = $repo->findByOrderBetween($from, $until, $field->getForm());
                    /** @var FormField $upfld */
                    foreach ($up as $upfld) {
                        $upfld->setOrder($upfld->getOrder() - 1);
                        $em->merge($upfld);
                    }
                    $em->merge($field);
                    $em->flush();
                } else {
                    $from = $oldOrder + $move;
                    $until = $oldOrder - 1;
                    $down = $repo->findByOrderBetween($from, $until, $field->getForm());
                    /** @var FormField $downfld */
                    foreach ($down as $downfld) {
                        $downfld->setOrder($downfld->getOrder() + 1);
                        $em->merge($downfld);
                    }
                    $em->merge($field);
                    $em->flush();
                }
                // trigger a table change in the view
                return $request->isXmlHttpRequest() ?
                    $this->render('admin/form/form_fields.html.twig', [
                        'formEntity' => $myForm,
                        'formFields' => $myFormFields,
                        'form' => $formView,
                        'modal' => $request->isXmlHttpRequest(),
                        'btns' => [$formView->offsetGet('close')],
                        'orders' => $orders,
                        'order' => $order,
                        'sort' => $sortKey,
                        'extend' => 'sortable-table.html.twig',
                    ]) :
                    $this->redirectToRoute('nav.admin_form_fields', ['form' => $myForm->getId()]);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage(), 'status' => 'failure'], Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->render('admin/form/form_fields.html.twig', [
            'formEntity' => $myForm,
            'formFields' => $myFormFields,
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'btns' => [$formView->offsetGet('close')],
            'orders' => $orders,
            'order' => $order,
            'sort' => $sortKey,
        ]);
    }

    /**
     * Controller for building a form report (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_reports/report/{form}/{id}", name="nav.admin_form_report")
     * @param integer $form
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminFormReportAction($form, $id = 0, Request $request)
    {

        return new Response();
    }

    /**
     * Controller for viewing form reports (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_reports/view/{form}/{id}", name="nav.admin_view_form_report")
     * @param integer $form
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
    public function adminViewFormReportAction($form, $id = 0, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Form $myForm */
        $myForm = $em->getRepository(Form::class)->find($form);
        if ($id) {
            $myFormReport = $em->getRepository(FormReport::class)->find($id);
        } else {
            $myFormReport = new FormReport();
            $myFormReport->setForm($myForm);
            foreach ($myForm->getFormFields() as $field)
                $myFormReport->addField($field->getId());
        }

        $form = $this->createForm(FormType::class, $myForm, [
            'attr' => ['action' => $request->getPathInfo()],
        ])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'label' => 'label.close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
            ]);

        $formView = $form->createView();

        return $this->render('admin/form/form_report.html.twig', [
            'formEntity' => $myForm,
            'formReport' => $myFormReport,
            'form' => $formView,
            'btns' => [$formView->offsetGet('close')],
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /**
     * Controller for viewing a form submission (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_submissions/submission/{id}", name="nav.admin_form_submission")
     * @param integer $id
     * @param Request $request
     * @return mixed
     */
     public function adminFormSubmissionAction($id, Request $request)
    {

        return new Response();
    }

    /**
     * Controller for building a form (admin only).
     *
     * @Route("/{_locale}/admin/forms/form_submissions/list/{form}", name="nav.admin_form_submissions")
     * @param integer $form
     * @param Request $request
     * @return mixed
     */
    public function adminFormSubmissionsAction($form, Request $request)
    {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $sortKey = $request->get('sort', $session->get('admin_form_submissions_sort_key', 'name'));
        $order = $request->get('order', $session->get('admin_form_submissions_sort_order', 'ASC'));

        $orders = [];
        foreach (['name', 'by', 'date',] as $key) {
            $orders[$key] = ($sortKey == $key ? ($order == 'ASC' ? 'DESC' : 'ASC') : $order);
        }
        $session->set('admin_form_submissions_sort_key', $sortKey);
        $session->set('admin_form_submissions_sort_order', $order);

        /** @var Form $myForm */
        $myForm = $em->getRepository(Form::class)->find($form);
        $submissions = $myForm->getSubmissions();
        $reports = $myForm->getReports();

        $form = $this->createForm(FormType::class, $myForm, [
            'attr' => ['action' => $request->getPathInfo()],
        ])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'label' => 'label.close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
            ]);

        $formView = $form->createView();

        return $this->render('admin/form/form_submissions.html.twig', [
            'formEntity' => $myForm,
            'form' => $formView,
            'btns' => [$formView->offsetGet('close')],
            'submissions' => $submissions,
            'reports' => $reports,
            'order' => $order,
            'orders' => $orders,
            'sort' => $sortKey,
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /**
     * @Route("/{_locale}/forms/list", name="nav.forms")
     */
    public function formsAction(Request $request)
    {
        return $this->render('forms/forms.html.twig');
    }

    /**
     * @Route("/{_locale}/forms/form/{id}", name="nav.form")
     */
    public function formAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Form $myForm */
        $myForm = $em->getRepository(Form::class)->find($id);

        // find a submission matching the form context
        $submission = $em->getRepository(FormSubmission::class)->findOneBy([
            'form' => $myForm,
            'createdBy' => $this->getUser()
        ]);
        if ($submission === null) {
            $submission = new FormSubmission();
            $submission->setForm($myForm);
            $submission->setCreatedBy($this->getUser());
        }

        $form = $this->createForm(ClientFormType::class, $submission);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($submission->getId())
                $em->merge($submission);
            else
                $em->persist($submission);
            $em->flush();
            return $this->redirectToRoute('nav.form', ['id' => $id]);
        }

        return $this->render('forms/form.html.twig',[
            'form' => $form->createView(),
            'myForm' => $myForm,
            ]);
    }
}