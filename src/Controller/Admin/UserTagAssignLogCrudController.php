<?php

namespace UserTagBundle\Controller\Admin;

use Tourze\EasyAdminExtraBundle\Controller\AbstractCrudController;
use UserTagBundle\Entity\AssignLog;

class UserTagAssignLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AssignLog::class;
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
