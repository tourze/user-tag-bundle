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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

/**
 * @extends AbstractCrudController<Tag>
 */
#[AdminCrud(
    routePath: '/user-tag/tag',
    routeName: 'user_tag_tag',
)]
final class UserTagTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户标签')
            ->setEntityLabelInPlural('用户标签')
            ->setPageTitle('index', '用户标签列表')
            ->setPageTitle('new', '创建用户标签')
            ->setPageTitle('edit', '编辑用户标签')
            ->setPageTitle('detail', '用户标签详情')
            ->setHelp('index', '管理用户标签，包括静态标签、智能标签和SQL标签')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'name', 'description'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('catalog', '标签分类')
            ->setRequired(false)
        ;

        yield ChoiceField::new('type', '标签类型')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => TagType::class])
            ->formatValue(function ($value) {
                return $value instanceof TagType ? $value->getLabel() : '';
            })
            ->setRequired(true)
        ;

        yield TextField::new('name', '标签名称')
            ->setRequired(true)
            ->setMaxLength(255)
        ;

        yield TextareaField::new('description', '标签描述')
            ->setRequired(false)
            ->hideOnIndex()
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
        $typeChoices = [];
        foreach (TagType::cases() as $case) {
            $typeChoices[$case->getLabel()] = $case->value;
        }

        return $filters
            ->add(TextFilter::new('name', '标签名称'))
            ->add(EntityFilter::new('catalog', '标签分类'))
            ->add(ChoiceFilter::new('type', '标签类型')->setChoices($typeChoices))
            ->add(BooleanFilter::new('valid', '是否有效'))
        ;
    }
}
