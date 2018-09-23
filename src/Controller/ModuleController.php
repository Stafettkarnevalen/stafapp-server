<?php

namespace App\Controller;

use App\Controller\Interfaces\ModalEventController;
use App\Entity\Modules\BaseModule;
use App\Entity\Modules\NewsModule;
use App\Entity\Modules\RedirectModule;
use App\Entity\Modules\TextModule;
use App\Entity\Security\SimpleACE;
use App\Form\Security\AccessControlListType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class ModuleController
 * @package App\Controller
 */
class ModuleController extends Controller implements ModalEventController
{
    /**
     * Controller for listing page modules.
     *
     * @Route("/{_locale}/admin/modules/{zone}/{page}", name="nav.admin_modules")
     * @param string $zone
     * @param string $page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminModulesAction($zone, $page, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var ArrayCollection $modules */
        $modules = $em->getRepository(BaseModule::class)->findBy([
            'page' => urldecode($page),
            'zone' => $zone,
            'parent' => null], ['order' => 'ASC']);

        $form = $this->createForm(FormType::class, null, [])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'label' => 'label.close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                    'onclick' => 'document.location.reload();'
                ],
            ])
            ->add('create', ButtonType::class, [
                'left_icon' => 'fa-edit',
                'right_icon' => 'fa-plus',
                'label' => 'label.create',
                'attr' => [
                    'class' => 'btn-success',
                    'value' => $this->generateUrl('nav.admin_module', [
                        'page' => $page,
                        'zone' => $zone,
                        'order' => count($modules),
                    ]),
                    'data-title' => $this->get('translator')->trans('label.create'),
                    'data-toggle' => 'modal',
                    'data-reload' => true,
                ],
            ]);

        $formView = $form->createView();
        return $this->render('admin/modules/modules.html.twig', [
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'modules' => $modules,
            'page' => $page,
            'zone' => $zone,
            'btns' => [$formView->offsetGet('close'), $formView->offsetGet('create')]
        ]);
    }

    /**
     * Controller for creating / editing a page module.
     *
     * @Route("/{_locale}/admin/module/{id}/{zone}/{page}/{order}", name="nav.admin_module")
     * @param integer $id
     * @param string $zone
     * @param string $page
     * @param integer $order
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminModuleAction($id = 0, $zone, $page, $order, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $module = null;
        if ($id) {
            $module = $em->getRepository(BaseModule::class)->find($id);
        } else {
            $module = new BaseModule();
            $module->setOrder($order)->setPage(urldecode($page))->setZone($zone)->setIsActive(true);
        }
        $form = $this->createForm(FormType::class, $module, [
            'attr' => ['action' => $request->getPathInfo()],
            'translation_domain' => 'module',
        ])
            ->add('page', HiddenType::class, [])
            ->add('zone', HiddenType::class, [])
            ->add('order', HiddenType::class, [])
            ->add('type', ChoiceType::class, [
                'choices' => [TextModule::class, NewsModule::class, RedirectModule::class],
                'choice_label' =>  function ($value) { return BaseModule::TYPE_NAMES[$value]; },
                'label' => 'label.module.type',
                'disabled' => $id != 0,
            ])
            ->add('title', TextType::class, [
                'label' => 'label.module.title',
            ])
            ->add('cssClass', TextType::class, [
                'label' => 'label.module.cssClass',
                'required' => false,
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'label.module.status',
                'expanded' => true,
                'choice_attr' => function () { return ['class' => 'inline']; },
                'label_attr' => ['class' => 'radio-inline'],
                'choices' => ['label.module.inactive' => 0, 'label.module.active' => 1],
            ])
            ->add('objectAces', AccessControlListType::class, [
                'label' => 'label.module.acl',
                'ace_translation_domain' => 'messages',
                'aces' => $module->getObjectAces(),
            ])
            ->add('close', ButtonType::class, [
                'left_icon' => 'fa-chevron-left',
                'right_icon' => 'fa-close',
                'label' => 'label.close',
                'attr' => [
                    'class' => 'btn-default',
                    'data-dismiss' => 'modal',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'left_icon' => 'fa-save',
                'right_icon' => 'fa-check',
                'label' => 'label.save',
                'attr' => ['class' => 'btn-success form-submit'],
            ])
        ;

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            if ($module->getId()) {
                $em->merge($module);
                $em->flush();

                /** @var MutableAclProviderInterface $aclProvider */
                $aclProvider = $this->get('security.acl.provider');
                $objectIdentity = $module->getObjectIdentity();
                /** @var MutableAclInterface $acl */
                $acl = $aclProvider->findAcl($objectIdentity);
                /** @var SimpleACE $ace */
                foreach ($module->getObjectAces() as $ace) {
                    if ($ace->getId()) {
                        $acl->updateObjectAce($ace->getIndex(), $ace->getMask());
                    } else {
                        $acl->insertObjectAce(new RoleSecurityIdentity($ace->getRole()), $ace->getMask(), $ace->getIndex());
                    }
                }
                $aclProvider->updateAcl($acl);
            } else {
                $module = $module->convert();
                $em->persist($module);
                $em->flush();

                /** @var MutableAclProviderInterface $aclProvider */
                $aclProvider = $this->get('security.acl.provider');
                $objectIdentity = $module->getObjectIdentity();
                /** @var MutableAclInterface $acl */
                $acl = $aclProvider->createAcl($objectIdentity);
                /** @var SimpleACE $ace */
                foreach ($module->getObjectAces() as $ace)
                    $acl->insertObjectAce(new RoleSecurityIdentity($ace->getRole()), $ace->getMask(), $ace->getIndex());
                $acl->insertObjectAce($module->getCreatedBy()->getUserSecurityIdentity(), MaskBuilder::MASK_OWNER, count($module->getObjectAces()));
                //foreach ($form->get('ObjectAces')->getData() as $ace) {
                    // $acl->insertObjectAce(new RoleSecurityIdentity($ace->))
                //}
                // $acl->insertObjectAce(new RoleSecurityIdentity('ROLE_ADMIN'), MaskBuilder::MASK_MASTER);
                $aclProvider->updateAcl($acl);
            }

            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirect(urldecode($page));

        } else if ($form->isSubmitted()) {
            $formView = $form->createView();
            return $this->render('admin/modules/module.html.twig', [
                'form' => $formView,
                'modal' => $request->isXmlHttpRequest(),
                'btns' => [$formView->offsetGet('close'), $formView->offsetGet('submit')]
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }
        $formView = $form->createView();
        return $this->render('admin/modules/module.html.twig', [
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'btns' => [$formView->offsetGet('close'), $formView->offsetGet('submit')]
        ]);
    }

    /**
     * Controller for creating / editing a page module.
     *
     * @Route("/{_locale}/admin/module/edit/{id}", name="nav.admin_module_edit")
     * @param integer $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminModuleEditAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var BaseModule $module */
        $module = $em->getRepository(BaseModule::class)->find($id);

        $form = $this->createForm($module->getFormClass(), $module, [
            'attr' => ['action' => $request->getPathInfo()],
            'delete_title' => $this->get('translator')->trans('label.delete', [], 'module'),
            'delete_path' => $this->generateUrl('nav.admin_module_delete', ['id' => $id]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->merge($module);
            $em->flush();
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirect($module->getPage());
        } else if ($form->isSubmitted()) {
            $formView = $form->createView();
            return $this->render('admin/modules/edit.html.twig', [
                'form' => $formView,
                'modal' => $request->isXmlHttpRequest(),
                'view' => $module->getFormView(),
                'btns' => [$formView->offsetGet('close'), $formView->offsetGet('delete'), $formView->offsetGet('submit')]
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }

        $formView = $form->createView();
        return $this->render('admin/modules/edit.html.twig', [
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'view' => $module->getFormView(),
            'btns' => [$formView->offsetGet('close'), $formView->offsetGet('delete'), $formView->offsetGet('submit')]
        ]);
    }

    /**
     * Controller for removing a page module.
     *
     * @Route("/{_locale}/admin/module/remove/{id}", name="nav.admin_module_delete")
     * @param integer $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminModuleDeleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var BaseModule $module */
        $module = $em->getRepository(BaseModule::class)->find($id);

        $fb = $this->createFormBuilder([], [
            'translation_domain' => 'module',
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
            $name = $module->getTitle();

            $after = $module->getSiblingsAfter($em);

            $em->remove($module);
            $em->flush();

            $this->get('session')->getFlashBag()
                ->add('success', [
                    'id' => 'flash.module.deleted',
                    'parameters' => ['%name%' => $name]
                ]);

            // return an empty response for the modal or a full rendered view for non-modal
            return $request->isXmlHttpRequest() ?
                new JsonResponse(['reloadPage' => 0], Response::HTTP_OK) :
                $this->redirectToRoute('nav.admin_modules');
        }

        return $this->render('admin/modules/delete.html.twig', [
            'module' => $module,
            'form' => $form->createView(),
            'modal' => $request->isXmlHttpRequest(),
        ]);
    }

    /**
     * Controller for creating / editing a child module.
     *
     * @Route("/{_locale}/admin/child_module/{parent}/{id}/{order}", name="nav.admin_child_module")
     * @param integer $parent
     * @param integer $id
     * @param integer $order
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminChildModuleAction($parent, $id = 0, $order, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var BaseModule $parent */
        $parent = $em->getRepository(BaseModule::class)->find($parent);
        /** @var BaseModule $module */
        $module = $parent->getChildInstance($order);

        $form = $this->createForm($module->getFormClass(), $module, [
            'attr' => ['action' => $request->getPathInfo()],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->merge($module);
            $em->flush();
            return $request->isXmlHttpRequest() ?
                new JsonResponse([], Response::HTTP_OK) :
                $this->redirect($module->getPage());
        } else if ($form->isSubmitted()) {
            $formView = $form->createView();
            return $this->render('admin/modules/edit.html.twig', [
                'form' => $formView,
                'view' => $module->getFormView(),
                'modal' => $request->isXmlHttpRequest(),
                'btns' => $id ?
                    [$formView->offsetGet('close'), $formView->offsetGet('delete'), $formView->offsetGet('submit')] :
                    [$formView->offsetGet('close'), $formView->offsetGet('submit')]
            ], new Response('', $request->isXmlHttpRequest() ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_OK));
        }

        $formView = $form->createView();
        return $this->render('admin/modules/edit.html.twig', [
            'form' => $formView,
            'view' => $module->getFormView(),
            'modal' => $request->isXmlHttpRequest(),
            'btns' => $id ?
                [$formView->offsetGet('close'), $formView->offsetGet('delete'), $formView->offsetGet('submit')] :
                [$formView->offsetGet('close'), $formView->offsetGet('submit')]
        ]);
    }

    /**
     * Controller viewing module.
     *
     * @Route("/{_locale}/module/{id}", name="nav.view_module")
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewModuleAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var BaseModule $module */
        $module = $em->getRepository(BaseModule::class)->find($id);
        $form = $this->createForm(FormType::class, $module, [
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
        return $this->render($module->getView(), [
            'module' => $module,
            'form' => $formView,
            'modal' => $request->isXmlHttpRequest(),
            'btns' => [$formView->offsetGet('close')]
        ]);
    }
}
