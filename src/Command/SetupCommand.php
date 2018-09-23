<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 27/06/2018
 * Time: 15.25
 */

namespace App\Command;

use App\Entity\Schools\SchoolType;
use App\Entity\Security\Group;
use App\Entity\Security\SystemUser;
use App\Entity\Security\User;
use App\Entity\Services\ServiceCategory;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupCommand extends ContainerAwareCommand
{

    private $admin = null;
    private $systemUsers = [];
    private $groups = [];
    private $serviceCategories = [];
    private $schoolTypes = [];

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:setup')

            // the short description shown while running "php bin/console list"
            ->setDescription('Performs the initial setup of the system.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to setup the system.')
        ;
    }

    protected function prepareUser(SymfonyStyle $io, string $title, int $id, string $defaultFirstname,
                                   string $defaultLastname, string $defaultUsername, array $roles, $password = true,
                                   $defaultPhone = null)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $vals = ['id' => $id];

        /** @var SystemUser $usr */
        $usr = $em->getRepository(SystemUser::class)->find($id);
        $ok = false;
        while (!$ok) {
            $io->text($title);
            $vals['firstname'] = $io->ask('Firstname', $usr ? $usr->getFirstname() : $defaultFirstname);
            $vals['lastname'] = $io->ask('Lastname', $usr ? $usr->getLastname() : $defaultLastname);
            $vals['username'] = $io->ask('Username (email)', $usr ? $usr->getUsername() : $defaultUsername);
            if ($password) {
                $vals['phone'] = $io->ask('Mobile phone', $usr ? $usr->getPhone() : $defaultPhone);
                $vals['password'] = $io->askHidden('Password', function($pwd) { return $pwd; });
            }

            foreach ($vals as $fld => $val) {
                if ($fld != 'password')
                    $io->text($fld . ': ' . $val);
            }
            $ok = $io->confirm('Is this correct');
        }
        $plainPassword = array_key_exists('password', $vals) ? $vals['password'] : null;
        unset($vals['password']);

        if (!$usr){
            $usr = new SystemUser();
            $usr
                ->setRoles($roles)
                ->setIsActive(true)
                ->setId($id)
            ;
        }

        $usr->fill($vals);
        if ($plainPassword)
            $usr->setPlainPassword($plainPassword);

        return $usr;
    }


    protected function prepareGroup(SymfonyStyle $io, string $title, int $id, string $defaultName,
                                    string $defaultEmail, array $roles, string $loginPath, string $logoutPath)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $vals = ['id' => $id];

        /** @var Group $grp */
        $grp = $em->getRepository(Group::class)->find($id);
        $ok = false;
        while (!$ok) {
            $io->text($title);
            $vals['name'] = $io->ask('Name', $grp ? $grp->getName(): $defaultName);
            $vals['email'] = $io->ask('Email', $grp ? $grp->getEmail() : $defaultEmail);
            foreach ($vals as $fld => $val) {
                $io->text($fld . ': ' . $val);
            }
            $ok = $io->confirm('Is this correct');
        }

        if (!$grp){
            $grp = new Group();
            $grp
                ->setIsSystem(true)
                ->setIsGoogleSynced(true)
                ->setRoles($roles)
                ->setLoginRoute($loginPath)
                ->setLogoutRoute($logoutPath)
                ->setId($id)
            ;
        }

        $grp->fill($vals);
        return $grp;
    }

    protected function prepareServiceCategory(SymfonyStyle $io, string $title, array $default)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $id = $default['id'];
        $vals = array_merge($default);

        /** @var ServiceCategory $cat */
        $cat = $em->getRepository(ServiceCategory::class)->find($id);
        $ok = false;
        while (!$ok) {
            $io->text($title);
            $vals['title'] = $io->ask('Title', $cat ? $cat->getTitle(): $default['title']);
            $vals['text'] = $io->ask('Text', $cat ? $cat->getText() : $default['text']);

            foreach ($vals as $fld => $val) {
                $io->text($fld . ': ' . $val);
            }
            $ok = $io->confirm('Is this correct');
        }

        if (!$cat){
            $cat = new ServiceCategory();
            $cat
                ->setId($id)
                ->setCreatedBy($this->admin)
            ;
        }

        $parent = $vals['parent'];
        unset($vals['parent']);
        if ($parent)
            $cat->setParent($this->serviceCategories[$parent]);
        else
            $cat->setParent(null);

        $cat->fill($vals);

        return $cat;
    }

    protected function prepareSchoolType(SymfonyStyle $io, string $title, array $default)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $id = $default['id'];
        $vals = array_merge($default);

        /** @var SchoolType $st */
        $st = $em->getRepository(SchoolType::class)->find($id);
        $ok = false;
        while (!$ok) {
            $io->text($title);
            $vals['name'] = $io->ask('Name', $st ? $st->getName(): $default['name']);
            $vals['abbreviation'] = $io->ask('Abbreviation', $st ? $st->getAbbreviation(): $default['abbreviation']);
            $vals['description'] = $io->ask('Description', $st ? $st->getAbbreviation(): $default['description']);
            $vals['minClassOf'] = $io->ask('Min class of', $st ? $st->getMinClassOf(): $default['minClassOf']);
            $vals['maxClassOf'] = $io->ask('Max class of', $st ? $st->getMaxClassOf(): $default['maxClassOf']);

            foreach ($vals as $fld => $val) {
                $io->text($fld . ': ' . $val);
            }
            $ok = $io->confirm('Is this correct');
        }

        if (!$st){
            $st = new SchoolType();
            $st
                ->setId($id)
                ->setCreatedBy($this->admin)
            ;
        }

        $group = $vals['group'];
        unset($vals['group']);
        if ($group)
            $st->setGroup($this->schoolTypes[$group]);
        else
            $st->setGroup(null);

        $st->fill($vals);

        return $st;
    }

    protected function setupSchoolTypes(InputInterface $input, OutputInterface $output)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $io = new SymfonyStyle($input, $output);

        // display title
        $io->title('Setup StafApp School Types');

        $types = [
            ['id' => 1, 'group' => null, 'name' => 'grundskolor årskurserna 1 - 6', 'abbreviation' => 'grundsk. 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 0, 'description' => 'grundskolor årskurserna 1 - 6'],
            ['id' => 2, 'group' => 1, 'name' => 'grundskolor årskurserna 1 - 6', 'abbreviation' => 'grundsk. 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 0, 'description' => 'grundskolor årskurserna 1 - 6 (med fler än 50 elever på årskurserna 3 - 6)'],
            ['id' => 3, 'group' => 1, 'name' => 'små grundskolor årskurserna 1 - 6', 'abbreviation' => 'små grunddsk. 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 1, 'description' => 'grundskolor årskurserna 1 - 6 (med färre än 50 elever på årskurserna 3 - 6)'],
            ['id' => 4, 'group' => 1, 'name' => 'språkbadsklasser årskurserna 1 - 6', 'abbreviation' => 'språkbad 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 2, 'description' => 'språkbadsklasser årskurserna 1 - 6 (med fler än 50 elever på årskurserna 3 - 6)'],
            ['id' => 5, 'group' => 1, 'name' => 'små språkbadsklasser årskurserna 1 - 6', 'abbreviation' => 'små språkbad 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 3, 'description' => 'språkbadsklasser årskurserna 1 - 6 (med färre än 50 elever på årskurserna 3 - 6)'],
            ['id' => 6, 'group' => 1, 'name' => 'inbjudna grundskolor årskurserna 1 - 6', 'abbreviation' => 'inbj. grundsk. 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 4, 'description' => 'inbjudna nordiska grundskolor årskurserna 1 - 6 (med fler än 50 elever på årskurserna 3 - 6)'],
            ['id' => 7, 'group' => 1, 'name' => 'små inbjudna grundskolor årskurserna 1 - 6', 'abbreviation' => 'små inbj. grundsk. 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 5, 'description' => 'inbjudna nordiska grundskolor årskurserna 1 - 6 (med färre än 50 elever på årskurserna 3 - 6)'],
            ['id' => 8, 'group' => null, 'name' => 'grundskolor årskurserna 7 - 9', 'abbreviation' => 'grundsk. 7-9', 'minClassOf' => 7, 'maxClassOf' => 9, 'order' => 1, 'description' => 'grundskolor årskurserna 7 - 9'],
            ['id' => 9, 'group' => 8, 'name' => 'grundskolor årskurserna 7 - 9', 'abbreviation' => 'grundsk. 7-9', 'minClassOf' => 7, 'maxClassOf' => 9, 'order' => 0, 'description' => 'grundskolor årskurserna 7 - 9'],
            ['id' => 10, 'group' => 8, 'name' => 'språkbadsklasser årskurserna 7 - 9', 'abbreviation' => 'språkbad 7-9', 'minClassOf' => 7, 'maxClassOf' => 9, 'order' => 6, 'description' => 'språkbadsklasser årskurserna 7 - 9'],
            ['id' => 11, 'group' => 8, 'name' => 'inbjudna grundskolor årskurserna 7 - 9', 'abbreviation' => 'inbj. grundsk. 7-9', 'minClassOf' => 7, 'maxClassOf' => 9, 'order' => 7, 'description' => 'inbjudna nordiska grundskolor årskurserna 7 - 9'],
            ['id' => 12, 'group' => null, 'name' => 'andra stadiet', 'abbreviation' => '2:a st.', 'minClassOf' => 10, 'maxClassOf' => 12, 'order' => 2, 'description' => 'andra stadiet årskurserna 10 - 12'],
            ['id' => 13, 'group' => 12, 'name' => 'andra stadiet', 'abbreviation' => '2:a st.', 'minClassOf' => 10, 'maxClassOf' => 12, 'order' => 0, 'description' => 'andra stadiet årskurserna 10 - 12'],
            ['id' => 14, 'group' => 12, 'name' => 'inbjudna andra stadiets skolor', 'abbreviation' => 'inbj. 2:a st.', 'minClassOf' => 10, 'maxClassOf' => 12, 'order' => 1, 'description' => 'inbjudna nordiska andra stadiets skolor årskurserna 10 - 12'],
            ['id' => 15, 'group' => null, 'name' => 'undervisning per verksamhetsområde åk 1 - 6', 'abbreviation' => 'u.p.v. 1-6', 'minClassOf' => 1, 'maxClassOf' => 6, 'order' => 3, 'description' => 'undervisning per verksamhetsområde åk 1 - 6'],
            ['id' => 16, 'group' => null, 'name' => 'undervisning per verksamhetsområde åk 7 - 9', 'abbreviation' => 'u.p.v. 7-9', 'minClassOf' => 7, 'maxClassOf' => 9, 'order' => 4, 'description' => 'undervisning per verksamhetsområde åk 7 - 9'],
            ['id' => 17, 'group' => null, 'name' => 'undervisning per verksamhetsområde andra stadiet', 'abbreviation' => 'u.p.v. 2:a st.', 'minClassOf' => 10, 'maxClassOf' => 12, 'order' => 12, 'description' => 'undervisning per verksamhetsområde andra stadiet årskurserna 10 - 12'],
        ];

        foreach ($types as $vals)
            $this->schoolTypes[$vals['id']] = $this->prepareSchoolType($io, 'Setting up school type: ' . $vals['name'], $vals);

        $io->progressStart(count($this->schoolTypes));

        foreach ($this->schoolTypes as $st) {
            if (!$em->contains($st)) {
                $em->persist($st);
            } else {
                $em->merge($st);
            }
            $em->flush();
            $io->progressAdvance(1);
        }
        $io->progressFinish();

    }


    protected function setupServiceCategories(InputInterface $input, OutputInterface $output)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $io = new SymfonyStyle($input, $output);

        // display title
        $io->title('Setup StafApp Service Categories');

        $categories = [
            ['id' => 1, 'parent' => null, 'title' => 'Stafettkarnevalen', 'text' => 'Stafettkarnevalen'],
            ['id' => 2, 'parent' => 1, 'title' => 'Stafetter', 'text' => 'Stafetter'],
            ['id' => 3, 'parent' => 1, 'title' => 'Hejarklackstävlingar', 'text' => 'Hejarklackstävlingar'],
            ['id' => 4, 'parent' => 1, 'title' => 'Inmarsch och maskottävlingar', 'text' => 'Inmarsch och maskottävlingar,'],
            ['id' => 5, 'parent' => 1, 'title' => 'Bespisning', 'text' => 'Bespisning'],
            ['id' => 6, 'parent' => 1, 'title' => 'Logi', 'text' => 'Logi'],
            ['id' => 7, 'parent' => 6, 'title' => 'Frukost i samband med logi', 'text' => 'Frukost i samband med logi'],
            ['id' => 8, 'parent' => 6, 'title' => 'Kvällsbit i samband med logi', 'text' => 'Kvällsbit i samband med logi'],
            ['id' => 9, 'parent' => 1, 'title' => 'Program', 'text' => 'Program'],
            ['id' => 10, 'parent' => null, 'title' => 'Mästerskap', 'text' => 'Skolidrottens mästerskap'],
            ['id' => 11, 'parent' => null, 'title' => 'Evenemang', 'text' => 'Skolidrottens evenemang'],
            ['id' => 12, 'parent' => null, 'title' => 'Idrott i skolan', 'text' => 'Idrott i skolan'],
            ['id' => 13, 'parent' => null, 'title' => 'Skolan i rörelse', 'text' => 'Skolan i rörelse'],
            ['id' => 14, 'parent' => null, 'title' => 'Medlemskap', 'text' => 'Medlemskap i Svenska Finlands Skolidrottsförbund'],
        ];

        foreach ($categories as $vals)
            $this->serviceCategories[$vals['id']] = $this->prepareServiceCategory($io, 'Setting up service category: ' . $vals['title'], $vals);

        $io->progressStart(count($this->serviceCategories));

        foreach ($this->serviceCategories as $cat) {
            if (!$em->contains($cat)) {
                $em->persist($cat);
            } else {
                $em->merge($cat);
            }
            $em->flush();
            $io->progressAdvance(1);
        }
        $io->progressFinish();
    }

    protected function setupSecurity(InputInterface $input, OutputInterface $output)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $io = new SymfonyStyle($input, $output);

        // display title
        $io->title('Setup StafApp Security Layer');

        $this->groups[1] = $this->prepareGroup($io, 'Setting up admin group', 1, 'Administratörer',
            'administratorer@stafapp.stafettkarnevalen.fi', ['ROLE_SUPER_ADMIN'], '/admin', '/');

        $this->groups[2] = $this->prepareGroup($io, 'Setting up school managers group', 2, 'Lagledare',
            'lagledare@stafapp.stafettkarnevalen.fi', ['ROLE_SCHOOL_MANAGER'], '/manager', '/');

        $this->groups[3] = $this->prepareGroup($io, 'Setting up school admins group', 3, 'Skoladministratörer',
            'skoladministratorer@stafapp.stafettkarnevalen.fi', ['ROLE_SCHOOL_ADMIN'], '/principal', '/');

        $this->groups[4] = $this->prepareGroup($io, 'Setting up principals group', 4, 'Rektorer',
            'rektorer@stafapp.stafettkarnevalen.fi', ['ROLE_SCHOOL_PRINCIPAL'], '/principal', '/');

        $this->groups[5] = $this->prepareGroup($io, 'Setting up cheerleading managers group', 5, 'Hejarklacksledare',
            'hejarklacksledare@stafapp.stafettkarnevalen.fi', ['ROLE_CHEERLEADING_MANAGER'], '/cheerleading', '/');

        $this->groups[6] = $this->prepareGroup($io, 'Setting up mascot competition managers group', 6, 'Maskottävlingsledare',
            'maskottavlingsledare@stafapp.stafettkarnevalen.fi', ['ROLE_MASCOT_MANAGER'], '/mascot', '/');

        $this->groups[7] = $this->prepareGroup($io, 'Setting up stewards group', 7, 'Funktionärer',
            'funktionarer@stafapp.stafettkarnevalen.fi', ['ROLE_STEWARD'], '/steward', '/');

        $this->groups[8] = $this->prepareGroup($io, 'Setting up leading stewards group', 8, 'Ledande funktionärer',
            'ledande.funktionarer@stafapp.stafettkarnevalen.fi', ['ROLE__LEADING_STEWARD'], '/steward', '/');

        $this->groups[9] = $this->prepareGroup($io, 'Setting up steward admins group', 9, 'Funktionärschefer',
            'funktionarschefer@stafapp.stafettkarnevalen.fi', ['ROLE_STEWARD_ADMIN'], '/steward', '/');

        $this->groups[10] = $this->prepareGroup($io, 'Setting up cup admins group', 10, 'Mästerskapsarrangörer',
            'masterskapsarrangorer@stafapp.stafettkarnevalen.fi', ['ROLE_CUP_ADMIN'], '/cup', '/');

        $this->systemUsers[1] = $this->prepareUser($io, 'Setting up guest user', 1, 'Anonym', 'Gäst',
            'noreply@stafapp.stafettkarnevalen.fi', ['ROLE_GUEST'], false);

        $this->systemUsers[2] = $this->prepareUser($io, 'Setting up admin user', 2, 'StafApp', 'Administratör',
            'webmaster@stafapp.stafettkarnevalen.fi', ['ROLE_SUPER_ADMIN'], true, '+358456096933');

        $io->text('Creating entities and updating the database');

        // displays a 100-step length progress bar
        $io->progressStart(count($this->groups) + count($this->systemUsers));

        foreach ($this->groups as $grp) {
            if (!$em->contains($grp)) {
                $em->persist($grp);
            } else {
                $em->merge($grp);
            }
            $em->flush();
            $io->progressAdvance(1);
        }

        foreach ($this->systemUsers as $usr) {
            if (!$em->contains($usr)) {
                $em->persist($usr);
            } else {
                $em->merge($usr);
            }
            $em->flush();
            $io->progressAdvance(1);
        }

        $this->systemUsers[2]->addGroup($this->groups[1]);
        $em->merge($this->systemUsers[2]);
        $em->flush();

        $io->progressFinish();

        $io->success('Security Layer successfully setup.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $systemUsers = $em->getRepository(SystemUser::class)->findAll();
        /** @var SystemUser $user */
        foreach ($systemUsers as $user)
            $this->systemUsers[$user->getId()] = $user;

        $groups = $em->getRepository(Group::class)->findAll();
        /** @var Group $group */
        foreach ($groups as $group)
            $this->groups[$group->getId()] = $group;

        $serviceCategories = $em->getRepository(ServiceCategory::class)->findAll();
        /** @var ServiceCategory $cat */
        foreach ($serviceCategories as $cat)
            $this->serviceCategories[$cat->getId()] = $cat;

        $schoolTypes = $em->getRepository(SchoolType::class)->findAll();
        /** @var SchoolType $st */
        foreach ($schoolTypes as $st)
            $this->schoolTypes[$st->getId()] = $st;

        $io = new SymfonyStyle($input, $output);
        $io->title('StafApp System Setup');


        $choices = ['q' => 'Quit', '1' => 'Setup Security Layer'];

        $action = null;
        do {
            if (!array_key_exists('2', $choices) && $this->admin = $em->getRepository(User::class)->find(2)) {
                $choices['2'] = 'Setup Service Categories';
                $choices['3'] = 'Setup School Types';
            }
            $action = $io->choice('Choose action', $choices, 'Quit');
            switch ($action) {
                case 'q':
                    break;
                case 1:
                    $this->setupSecurity($input, $output);
                    break;
                case 2:
                    $this->setupServiceCategories($input, $output);
                    break;
                case 3:
                    $this->setupSchoolTypes($input, $output);
                    break;
            }
        } while ($action != 'q');
    }
}