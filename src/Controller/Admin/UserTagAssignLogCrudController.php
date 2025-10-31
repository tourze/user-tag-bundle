<?php

namespace UserTagBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use UserTagBundle\Entity\AssignLog;

/**
 * @extends AbstractCrudController<AssignLog>
 */
#[AdminCrud(
    routePath: '/user-tag/assign-log',
    routeName: 'user_tag_assign_log',
)]
final class UserTagAssignLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AssignLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('打标记录')
            ->setEntityLabelInPlural('打标记录')
            ->setPageTitle('index', '打标记录列表')
            ->setPageTitle('new', '创建打标记录')
            ->setPageTitle('edit', '编辑打标记录')
            ->setPageTitle('detail', '打标记录详情')
            ->setHelp('index', '查看用户标签的绑定和解绑记录')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'userId'])
            ->setPaginatorPageSize(30)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('tag', '标签')
            ->setRequired(true)
            ->autocomplete()
        ;

        yield TextField::new('userId', '用户标识符')
            ->setRequired(true)
            ->setMaxLength(255)
        ;

        yield AssociationField::new('user', '用户对象')
            ->setRequired(false)
            ->hideOnIndex()
            ->autocomplete()
        ;

        yield DateTimeField::new('assignTime', '绑定时间')
            ->setRequired(false)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('unassignTime', '解绑时间')
            ->setRequired(false)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield BooleanField::new('valid', '是否有效')
            ->renderAsSwitch(false)
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('userId', '用户标识符'))
            ->add(EntityFilter::new('tag', '标签'))
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(DateTimeFilter::new('assignTime', '绑定时间'))
            ->add(DateTimeFilter::new('unassignTime', '解绑时间'))
        ;
    }
}
