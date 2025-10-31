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
use UserTagBundle\Entity\SmartRule;

/**
 * @extends AbstractCrudController<SmartRule>
 */
#[AdminCrud(
    routePath: '/user-tag/smart-rule',
    routeName: 'user_tag_smart_rule',
)]
final class UserTagSmartRuleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SmartRule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('智能标签规则')
            ->setEntityLabelInPlural('智能标签规则')
            ->setPageTitle('index', '智能标签规则列表')
            ->setPageTitle('new', '创建智能标签规则')
            ->setPageTitle('edit', '编辑智能标签规则')
            ->setPageTitle('detail', '智能标签规则详情')
            ->setHelp('index', '智能标签的定时执行规则，通过JSON配置执行条件')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'cronStatement'])
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

        yield CodeEditorField::new('jsonStatement', 'JSON规则')
            ->setLanguage('javascript')
            ->setRequired(true)
            ->hideOnIndex()
            ->setHelp('定义标签的智能匹配规则，JSON格式')
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
