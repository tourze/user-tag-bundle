<?php

namespace UserTagBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * 为测试环境创建所需的数据库表
 *
 * 这个Fixture仅在测试环境中运行，用于确保所有必需的表都存在
 */
#[When(env: 'test')]
class TestSchemaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 获取EntityManager而不是直接使用ObjectManager
        if (!method_exists($manager, 'getConnection')) {
            return;
        }

        /** @var Connection $connection */
        $connection = $manager->getConnection();

        // 使用原生SQL创建表，避免复杂的DBAL Schema API
        $this->createUserTagTables($connection);
        $this->createDependencyTables($connection);
    }

    private function createUserTagTables(Connection $connection): void
    {
        // 创建用户标签分类表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS crm_tag_category (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(60) NOT NULL,
                description TEXT,
                create_time DATETIME,
                update_time DATETIME,
                created_by VARCHAR(255),
                updated_by VARCHAR(255)
            )
        ');

        // 创建用户标签表
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS crm_tag (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(60) NOT NULL,
                category_id INTEGER NOT NULL,
                type VARCHAR(20) DEFAULT '',
                description TEXT,
                create_time DATETIME,
                update_time DATETIME,
                created_by VARCHAR(255),
                updated_by VARCHAR(255),
                FOREIGN KEY (category_id) REFERENCES crm_tag_category(id)
            )
        ");

        // 创建用户标签分配记录表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS crm_tag_user (
                id VARCHAR(19) PRIMARY KEY,
                tag_id INTEGER NOT NULL,
                user_ref_id INTEGER,
                user_id VARCHAR(255) NOT NULL,
                assign_time DATETIME,
                unassign_time DATETIME,
                valid BOOLEAN DEFAULT 0,
                create_time DATETIME,
                update_time DATETIME,
                created_by VARCHAR(255),
                updated_by VARCHAR(255),
                created_from_ip VARCHAR(45),
                updated_from_ip VARCHAR(45),
                FOREIGN KEY (tag_id) REFERENCES crm_tag(id),
                UNIQUE(tag_id, user_id)
            )
        ');

        // 创建智能规则表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS ims_user_tag_smart_rule (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tag_id INTEGER NOT NULL,
                cron_statement VARCHAR(60) NOT NULL,
                json_statement TEXT NOT NULL,
                create_time DATETIME,
                update_time DATETIME,
                created_by VARCHAR(255),
                updated_by VARCHAR(255),
                FOREIGN KEY (tag_id) REFERENCES crm_tag(id) ON DELETE CASCADE,
                UNIQUE(tag_id)
            )
        ');

        // 创建SQL规则表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS ims_user_tag_sql_rule (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tag_id INTEGER NOT NULL,
                cron_statement VARCHAR(60) NOT NULL,
                sql_statement TEXT NOT NULL,
                create_time DATETIME,
                update_time DATETIME,
                created_by VARCHAR(255),
                updated_by VARCHAR(255),
                FOREIGN KEY (tag_id) REFERENCES crm_tag(id) ON DELETE CASCADE,
                UNIQUE(tag_id)
            )
        ');
    }

    private function createDependencyTables(Connection $connection): void
    {
        // 创建biz_user_biz_role表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS biz_user_biz_role (
                biz_user_id INTEGER,
                biz_role_id INTEGER,
                PRIMARY KEY (biz_user_id, biz_role_id)
            )
        ');

        // 创建biz_user表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS biz_user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(120) NOT NULL,
                email VARCHAR(255),
                password_hash VARCHAR(255)
            )
        ');

        // 创建biz_role表
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS biz_role (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255)
            )
        ');
    }

    public function getOrder(): int
    {
        // 确保在其他fixture之前运行
        return -100;
    }
}
