<?php

namespace UserTagBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use UserTagBundle\Entity\SmartRule;

class UserTagSmartRuleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SmartRule::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
