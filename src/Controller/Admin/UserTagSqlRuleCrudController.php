<?php

namespace UserTagBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use UserTagBundle\Entity\SqlRule;

/**
 * @extends AbstractCrudController<SqlRule>
 */
#[AdminCrud(
    routePath: '/user-tag/sql-rule',
    routeName: 'user_tag_sql_rule',
)]
final class UserTagSqlRuleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SqlRule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('SQL标签规则')
            ->setEntityLabelInPlural('SQL标签规则')
            ->setPageTitle('index', 'SQL标签规则列表')
            ->setPageTitle('new', '创建SQL标签规则')
            ->setPageTitle('edit', '编辑SQL标签规则')
            ->setPageTitle('detail', 'SQL标签规则详情')
            ->setHelp('index', 'SQL标签的定时执行规则，通过SQL查询执行标签分配')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'cronStatement', 'sqlStatement'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('tag', '所属标签')
            ->setRequired(true)
            ->autocomplete()
        ;

        yield TextField::new('cronStatement', '定时表达式')
            ->setRequired(true)
            ->setMaxLength(60)
            ->setHelp('使用Cron表达式设置执行时间，例如: 0 2 * * * 表示每天凌晨02点执行')
        ;

        yield CodeEditorField::new('sqlStatement', 'SQL语句')
            ->setLanguage('sql')
            ->setRequired(true)
            ->hideOnIndex()
            ->setHelp('定义标签的SQL查询规则，返回的结果集将被打上标签')
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
            ->add(EntityFilter::new('tag', '所属标签'))
            ->add(TextFilter::new('cronStatement', '定时表达式'))
        ;
    }
}
