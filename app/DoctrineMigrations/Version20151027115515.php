<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151027115515 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `procedure`');
        $this->addSql('DROP TABLE tipo_documento');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `procedure` (id_procedure INT AUTO_INCREMENT NOT NULL, user_id_user INT DEFAULT NULL, procedure_type_id_procedure_type INT DEFAULT NULL, employer_id_employer INT DEFAULT NULL, INDEX fk_procedure_procedure_type1 (procedure_type_id_procedure_type), INDEX fk_procedure_user1 (user_id_user), INDEX fk_procedure_employer1 (employer_id_employer), PRIMARY KEY(id_procedure)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_documento (id_tipo_documento INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id_tipo_documento)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
