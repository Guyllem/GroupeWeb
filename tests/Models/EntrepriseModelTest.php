<?php
namespace Tests\Models;

use App\Models\EnterpriseModel;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class EnterpriseModelTest extends TestCase
{
    private $dbMock;
    private $pdoMock;
    private $pdoStatementMock;
    private $enterpriseModel;

    protected function setUp(): void
    {
        // Configuration des mocks
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);
        $this->pdoMock = $this->createMock(PDO::class);
        $this->dbMock = $this->createMock(\App\Database::class);

        // Configuration du comportement du mock Database
        $this->dbMock->method('connect')->willReturn($this->pdoMock);

        // Instanciation du modèle avec le mock
        $this->enterpriseModel = new EnterpriseModel($this->dbMock);
    }

    public function testGetTopRatedEnterprises()
    {
        // Données attendues basées sur le dump SQL
        $expectedEnterprises = [
            [
                'Id_Entreprise' => 5,
                'Nom_Entreprise' => 'IoTConnect',
                'Description_Entreprise' => 'Spécialiste des objets connectés et de la domotique',
                'Email_Entreprise' => 'contact@iotconnect.com',
                'Telephone_Entreprise' => '0388756421',
                'Effectif_Entreprise' => 60,
                'rating' => 4.5
            ]
        ];

        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetchAll')->willReturn($expectedEnterprises);
        $this->pdoStatementMock->method('execute')->willReturn(true);

        // Configuration du mock pour getEnterpriseSectors
        $sectorsMock = $this->createMock(PDOStatement::class);
        $sectorsMock->method('fetchAll')->willReturn([
            ['Id_Secteur' => 6, 'Nom_Secteur' => 'IoT'],
            ['Id_Secteur' => 8, 'Nom_Secteur' => 'Réseaux']
        ]);

        // Simulation des différents appels à prepare
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($sectorsMock) {
                if (strpos($query, 'Secteur') !== false) {
                    return $sectorsMock;
                }
                return $this->pdoStatementMock;
            }));

        // Exécution de la méthode à tester
        $result = $this->enterpriseModel->getTopRatedEnterprises(1, 0);

        // Vérification du résultat
        $this->assertNotEmpty($result);
    }

    public function testGetEnterpriseDetails()
    {
        // Données attendues basées sur le dump SQL
        $expectedEnterprise = [
            'Id_Entreprise' => 1,
            'Nom_Entreprise' => 'TechInno',
            'Description_Entreprise' => 'Entreprise spécialisée dans le développement de solutions innovantes pour l\'industrie 4.0',
            'Email_Entreprise' => 'contact@techinno.fr',
            'Telephone_Entreprise' => '0145789632',
            'Effectif_Entreprise' => 120,
            'Id_Localisation' => 1,
            'Ville_Localisation' => 'Paris',
            'Code_Postal_Localisation' => 75008,
            'Adresse_Localisation' => '23 Rue du Faubourg Saint-Honoré',
            'rating' => 4.5,
            'rating_count' => 2
        ];

        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetch')->willReturn($expectedEnterprise);
        $this->pdoStatementMock->method('fetchColumn')->willReturn(3); // Nombre d'offres
        $this->pdoStatementMock->method('execute')->willReturn(true);

        // Configuration du mock pour getEnterpriseSectors
        $sectorsMock = $this->createMock(PDOStatement::class);
        $sectorsMock->method('fetchAll')->willReturn([
            ['Id_Secteur' => 4, 'Nom_Secteur' => 'Cloud Computing'],
            ['Id_Secteur' => 6, 'Nom_Secteur' => 'IoT'],
            ['Id_Secteur' => 7, 'Nom_Secteur' => 'Big Data']
        ]);

        // Simulation des différents appels à prepare
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($sectorsMock) {
                if (strpos($query, 'Secteur') !== false) {
                    return $sectorsMock;
                }
                if (strpos($query, 'COUNT') !== false) {
                    return $this->pdoStatementMock;
                }
                return $this->pdoStatementMock;
            }));

        // Exécution de la méthode à tester
        $result = $this->enterpriseModel->getEnterpriseDetails(1);

        // Vérification du résultat
        $this->assertEquals($expectedEnterprise['Nom_Entreprise'], $result['Nom_Entreprise']);
    }

    public function testRateEnterprise()
    {
        // Cas où l'entreprise a déjà été évaluée
        $this->pdoStatementMock->method('fetchColumn')->willReturn(1);
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Exécution de la méthode à tester
        $result = $this->enterpriseModel->rateEnterprise(1, 4, 5);

        // Vérification du résultat
        $this->assertTrue($result);
    }
}