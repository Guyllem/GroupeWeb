<?php
namespace Tests\Models;

use App\Models\StudentModel;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class StudentModelTest extends TestCase
{
    private $dbMock;
    private $pdoMock;
    private $pdoStatementMock;
    private $studentModel;

    protected function setUp(): void
    {
        // Configuration des mocks
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);
        $this->pdoMock = $this->createMock(PDO::class);
        $this->dbMock = $this->createMock(\App\Database::class);

        // Configuration du comportement du mock Database
        $this->dbMock->method('connect')->willReturn($this->pdoMock);

        // Instanciation du modèle avec le mock
        $this->studentModel = new StudentModel($this->dbMock);
    }

    public function testGetStudentInfo()
    {
        // Données attendues basées sur le dump SQL
        $expectedStudent = [
            'Id_Etudiant' => 1,
            'Id_Utilisateur' => 4,
            'Nom_Utilisateur' => 'Leroy',
            'Prenom_Utilisateur' => 'Emma',
            'Email_Utilisateur' => 'etudiant1@cesi.fr',
            'Nom_Promotion' => 'RISR25',
            'Id_Promotion' => 1,
            'Nom_Campus' => 'CESI Bordeaux',
            'Id_Campus' => 1
        ];

        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetch')->willReturn($expectedStudent);
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Exécution de la méthode à tester
        $result = $this->studentModel->getStudentInfo(1);

        // Vérification du résultat
        $this->assertEquals($expectedStudent, $result);
    }

    public function testGetStudentSkills()
    {
        // Données attendues basées sur le dump SQL
        $expectedSkills = [
            ['Id_Competence' => 9, 'Nom_Competence' => 'Cybersécurité'],
            ['Id_Competence' => 10, 'Nom_Competence' => 'Réseau'],
            ['Id_Competence' => 13, 'Nom_Competence' => 'Azure']
        ];

        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetchAll')->willReturn($expectedSkills);
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Exécution de la méthode à tester
        $result = $this->studentModel->getStudentSkills(1);

        // Vérification du résultat
        $this->assertEquals($expectedSkills, $result);
    }

    public function testAddToWishlist()
    {
        // Configuration du comportement des mocks pour simuler une offre non encore ajoutée
        $this->pdoStatementMock->method('fetchColumn')->willReturn(0);
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Exécution de la méthode à tester
        $result = $this->studentModel->addToWishlist(1, 2);

        // Vérification du résultat
        $this->assertTrue($result);
    }

    public function testRemoveFromWishlist()
    {
        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Exécution de la méthode à tester
        $result = $this->studentModel->removeFromWishlist(1, 3);

        // Vérification du résultat
        $this->assertTrue($result);
    }
}