<?php
namespace Tests\Models;

use App\Models\OfferModel;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class OfferModelTest extends TestCase
{
    private $dbMock;
    private $pdoMock;
    private $pdoStatementMock;
    private $offerModel;

    protected function setUp(): void
    {
        // Configuration des mocks
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);
        $this->pdoMock = $this->createMock(PDO::class);
        $this->dbMock = $this->createMock(\App\Database::class);

        // Configuration du comportement du mock Database
        $this->dbMock->method('connect')->willReturn($this->pdoMock);

        // Instanciation du modèle avec le mock
        $this->offerModel = new OfferModel($this->dbMock);
    }

    public function testGetRecentOffers()
    {
        // Données attendues basées sur le dump SQL
        $expectedOffers = [
            [
                'Id_Offre' => 7,
                'Titre_Offre' => 'Stage Intelligence Artificielle',
                'Description_Offre' => 'Recherche et développement dans le domaine du deep learning et traitement du langage naturel.',
                'Remuneration_Offre' => 1100,
                'Niveau_Requis_Offre' => 'Bac+5',
                'Date_Debut_Offre' => '2025-02-01',
                'Duree_Min_Offre' => 5,
                'Duree_Max_Offre' => 6,
                'Id_Entreprise' => 7,
                'Nom_Entreprise' => 'AILab'
            ]
        ];

        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetchAll')->willReturn($expectedOffers);
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Configuration du mock pour getOfferSkills
        $skillsMock = $this->createMock(PDOStatement::class);
        $skillsMock->method('fetchAll')->willReturn([
            ['Id_Competence' => 5, 'Nom_Competence' => 'Python'],
            ['Id_Competence' => 6, 'Nom_Competence' => 'Machine Learning']
        ]);

        // Simulation de l'appel à getOfferSkills dans getRecentOffers
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($skillsMock) {
                if (strpos($query, 'Necessiter') !== false) {
                    return $skillsMock;
                }
                return $this->pdoStatementMock;
            }));

        // Exécution de la méthode à tester
        $result = $this->offerModel->getRecentOffers(1, 0);

        // Vérification que le résultat contient au moins un élément
        $this->assertNotEmpty($result);
    }

    public function testGetOfferDetails()
    {
        // Données attendues basées sur le dump SQL
        $expectedOffer = [
            'Id_Offre' => 3,
            'Titre_Offre' => 'Stage Cybersécurité',
            'Description_Offre' => 'Intégrez notre équipe sécurité pour participer à l\'audit et au renforcement de nos infrastructures.',
            'Remuneration_Offre' => 900,
            'Niveau_Requis_Offre' => 'Bac+3/4',
            'Date_Debut_Offre' => '2025-03-01',
            'Duree_Min_Offre' => 3,
            'Duree_Max_Offre' => 6,
            'Id_Entreprise' => 4,
            'Nom_Entreprise' => 'CloudSecure'
        ];

        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetch')->willReturn($expectedOffer);
        $this->pdoStatementMock->method('execute')->willReturn(true);

        // Configuration du mock pour getOfferSkills
        $skillsMock = $this->createMock(PDOStatement::class);
        $skillsMock->method('fetchAll')->willReturn([
            ['Id_Competence' => 9, 'Nom_Competence' => 'Cybersécurité'],
            ['Id_Competence' => 10, 'Nom_Competence' => 'Réseau']
        ]);

        // Simulation des différents appels à prepare
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($skillsMock) {
                if (strpos($query, 'Necessiter') !== false) {
                    return $skillsMock;
                }
                return $this->pdoStatementMock;
            }));

        // Exécution de la méthode à tester
        $result = $this->offerModel->getOfferDetails(3);

        // Vérification du résultat
        $this->assertEquals($expectedOffer['Titre_Offre'], $result['Titre_Offre']);
    }

    public function testIsInWishlist()
    {
        // Configuration du comportement des mocks
        $this->pdoStatementMock->method('fetchColumn')->willReturn(1); // L'offre est dans la wishlist
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Exécution de la méthode à tester
        $result = $this->offerModel->isInWishlist(3, 1);

        // Vérification du résultat
        $this->assertTrue($result);
    }
}