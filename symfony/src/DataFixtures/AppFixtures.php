<?php

namespace App\DataFixtures;

use App\Entity\Notebook;
use App\Entity\ShareMap;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /** @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $test1 = new User();
        $test1->setEmail("test1@angle.mx");
        $test1->setFirstName("Angle");
        $test1->setLastName("Webmaster");
        $test1->setPassword($this->passwordEncoder->encodePassword(
            $test1,
            'angleroot'
        ));
        $test1->setRoles(['ROLE_SUPER_ADMIN']);
        $manager->persist($test1);

        $test2 = new User();
        $test2->setEmail("test2@angle.mx");
        $test2->setFirstName("Angle");
        $test2->setLastName("Webmaster");
        $test2->setPassword($this->passwordEncoder->encodePassword(
            $test2,
            'angleroot'
        ));
        $test2->setRoles(['ROLE_SUPER_ADMIN']);
        $manager->persist($test2);

        $testNotebook = new Notebook();
        $testNotebook->setUser($test1);
        $testNotebook->setName('Test Notebook');
        $manager->persist($testNotebook);

        $testShareMap = new ShareMap();
        $testShareMap->setUser($test2);
        $testShareMap->setNotebook($testNotebook);
        $testShareMap->setInviteEmail($test2->getEmail());
        $manager->persist($testShareMap);

        $manager->flush();
    }
}
