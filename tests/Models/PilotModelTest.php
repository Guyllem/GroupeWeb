<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\PilotModel;
use App\Database;
use PDO;
use PDOStatement;

/**
 * Classe de tests unitaires pour PilotModel
 */
class PilotModelTest extends TestCase
{
    /**
     * @var PilotModel
     */
    private $pilotModel;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dbMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $pdoMock;

    /**
     * Configuration initiale avant chaque test
     */
    protected function setUp(): void
    {
        // Créer des mocks pour la base de données et PDO
        $this->dbMock = $this->createMock(Database::class);
        $this->pdoMock = $this->createMock(PDO::class);

        // Configurer le mock de Database pour qu'il retourne le mock de PDO
        $this->dbMock->method('connect')->willReturn($this->pdoMock);

        // Initialiser le PilotModel avec le mock de Database
        $this->pilotModel = new PilotModel($this->dbMock);
    }

    /**
     * Test de la méthode getPilotsByName
     */
    public function testGetPilotsByName()
    {
        // Créer les données de test
        $expectedPilots = [
            [
                'Id_Pilote' => 1,
                'Id_Utilisateur' => 2,
                'Nom_Utilisateur' => 'Martin',
                'Prenom_Utilisateur' => 'Sophie',
                'Nom_Campus' => 'CESI Bordeaux',
                'Id_Campus' => 1,
                'promotion_count' => 3
            ],
            [
                'Id_Pilote' => 2,
                'Id_Utilisateur' => 3,
                'Nom_Utilisateur' => 'Dubois',
                'Prenom_Utilisateur' => 'Pierre',
                'Nom_Campus' => 'CESI Toulouse',
                'Id_Campus' => 5,
                'promotion_count' => 2
            ]
        ];

        // Créer un mock de PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchAll')->willReturn($expectedPilots);

        // Configurer le mock de PDO pour qu'il retourne le mock de PDOStatement
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        // Exécuter la méthode à tester
        $result = $this->pilotModel->getPilotsByName();

        // Vérifier que le résultat est celui attendu
        $this->assertEquals($expectedPilots, $result);
    }

    /**
     * Test de la méthode getPilotDetails
     */
    public function testGetPilotDetails()
    {
        // Créer les données de test
        $pilotId = 1;
        $pilotDetails = [
            'Id_Pilote' => 1,
            'Id_Utilisateur' => 2,
            'Nom_Utilisateur' => 'Martin',
            'Prenom_Utilisateur' => 'Sophie',
            'Email_Utilisateur' => 'pilote1@cesi.fr',
            'Nom_Campus' => 'CESI Bordeaux',
            'Id_Campus' => 1
        ];

        $pilotPromotions = [
            [
                'Id_Promotion' => 1,
                'Nom_Promotion' => 'RISR25',
                'Specialite_Promotion' => 'Réseaux et Sécurité',
                'Statut_Promotion' => 'Active',
                'Niveau_Promotion' => 'Bac+5',
                'Date_Debut_Superviser' => '2023-09-01',
                'Date_Fin_Superviser' => '2025-07-31',
                'Nom_Campus' => 'CESI Bordeaux',
                'Id_Campus' => 1
            ],
            [
                'Id_Promotion' => 2,
                'Nom_Promotion' => 'SYSNUM24',
                'Specialite_Promotion' => 'Systèmes Numériques',
                'Statut_Promotion' => 'Active',
                'Niveau_Promotion' => 'Bac+3',
                'Date_Debut_Superviser' => '2023-09-01',
                'Date_Fin_Superviser' => '2025-07-31',
                'Nom_Campus' => 'CESI Lyon',
                'Id_Campus' => 2
            ]
        ];

        // Créer les mocks de PDOStatement
        $detailsStmtMock = $this->createMock(PDOStatement::class);
        $detailsStmtMock->method('execute')->willReturn(true);
        $detailsStmtMock->method('fetch')->willReturn($pilotDetails);

        $promotionsStmtMock = $this->createMock(PDOStatement::class);
        $promotionsStmtMock->method('execute')->willReturn(true);
        $promotionsStmtMock->method('fetchAll')->willReturn($pilotPromotions);

        // Configurer le mock de PDO pour qu'il retourne les mocks de PDOStatement
        $this->pdoMock->method('prepare')
            ->will($this->onConsecutiveCalls($detailsStmtMock, $promotionsStmtMock));

        // Exécuter la méthode à tester
        $result = $this->pilotModel->getPilotDetails($pilotId);

        // Vérifier le résultat
        $expectedResult = $pilotDetails;
        $expectedResult['promotions'] = $pilotPromotions;

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test de la méthode getSupervisedPromotions
     */
    public function testGetSupervisedPromotions()
    {
        // Créer les données de test
        $pilotId = 1;
        $expectedPromotions = [
            [
                'Id_Promotion' => 1,
                'Nom_Promotion' => 'RISR25',
                'Specialite_Promotion' => 'Réseaux et Sécurité',
                'Statut_Promotion' => 'Active',
                'Niveau_Promotion' => 'Bac+5',
                'Date_Debut_Superviser' => '2023-09-01',
                'Date_Fin_Superviser' => '2025-07-31',
                'Nom_Campus' => 'CESI Bordeaux',
                'Id_Campus' => 1
            ],
            [
                'Id_Promotion' => 2,
                'Nom_Promotion' => 'SYSNUM24',
                'Specialite_Promotion' => 'Systèmes Numériques',
                'Statut_Promotion' => 'Active',
                'Niveau_Promotion' => 'Bac+3',
                'Date_Debut_Superviser' => '2023-09-01',
                'Date_Fin_Superviser' => '2025-07-31',
                'Nom_Campus' => 'CESI Lyon',
                'Id_Campus' => 2
            ]
        ];

        // Créer un mock de PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchAll')->willReturn($expectedPromotions);

        // Configurer le mock de PDO pour qu'il retourne le mock de PDOStatement
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        // Exécuter la méthode à tester
        $result = $this->pilotModel->getSupervisedPromotions($pilotId);

        // Vérifier que le résultat est celui attendu
        $this->assertEquals($expectedPromotions, $result);
    }

    /**
     * Test de la méthode getSupervisedStudents
     */
    public function testGetSupervisedStudents()
    {
        // Créer les données de test
        $pilotId = 1;
        $expectedStudents = [
            [
                'Id_Etudiant' => 1,
                'Id_Utilisateur' => 4,
                'Nom_Utilisateur' => 'Leroy',
                'Prenom_Utilisateur' => 'Emma',
                'Id_Promotion' => 1,
                'Nom_Promotion' => 'RISR25',
                'Specialite_Promotion' => 'Réseaux et Sécurité',
                'Id_Campus' => 1,
                'Nom_Campus' => 'CESI Bordeaux',
                'application_count' => 1,
                'wishlist_count' => 2
            ],
            [
                'Id_Etudiant' => 2,
                'Id_Utilisateur' => 5,
                'Nom_Utilisateur' => 'Petit',
                'Prenom_Utilisateur' => 'Thomas',
                'Id_Promotion' => 2,
                'Nom_Promotion' => 'SYSNUM24',
                'Specialite_Promotion' => 'Systèmes Numériques',
                'Id_Campus' => 2,
                'Nom_Campus' => 'CESI Lyon',
                'application_count' => 1,
                'wishlist_count' => 3
            ]
        ];

        // Créer un mock de PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchAll')->willReturn($expectedStudents);

        // Configurer le mock de PDO pour qu'il retourne le mock de PDOStatement
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        // Exécuter la méthode à tester
        $result = $this->pilotModel->getSupervisedStudents($pilotId);

        // Vérifier que le résultat est celui attendu
        $this->assertEquals($expectedStudents, $result);
    }

    /**
     * Test de la méthode searchStudents
     */
    public function testSearchStudents()
    {
        // Créer les données de test
        $searchTerm = 'Emma';
        $pilotId = 1;
        $expectedStudents = [
            [
                'Id_Etudiant' => 1,
                'Id_Utilisateur' => 4,
                'Nom_Utilisateur' => 'Leroy',
                'Prenom_Utilisateur' => 'Emma',
                'Id_Promotion' => 1,
                'Nom_Promotion' => 'RISR25',
                'Specialite_Promotion' => 'Réseaux et Sécurité',
                'Id_Campus' => 1,
                'Nom_Campus' => 'CESI Bordeaux',
                'application_count' => 1,
                'wishlist_count' => 2
            ]
        ];

        // Créer un mock de PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchAll')->willReturn($expectedStudents);

        // Configurer le mock de PDO pour qu'il retourne le mock de PDOStatement
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        // Exécuter la méthode à tester
        $result = $this->pilotModel->searchStudents($searchTerm, $pilotId);

        // Vérifier que le résultat est celui attendu
        $this->assertEquals($expectedStudents, $result);
    }

    /**
     * Test de la méthode getPilotIdFromUserId
     */
    public function testGetPilotIdFromUserId()
    {
        // Créer les données de test
        $userId = 2;
        $expectedPilotId = ['Id_Pilote' => 1];

        // Créer un mock de PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetch')->willReturn($expectedPilotId);

        // Configurer le mock de PDO pour qu'il retourne le mock de PDOStatement
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        // Exécuter la méthode à tester
        $result = $this->pilotModel->getPilotIdFromUserId($userId);

        // Vérifier que le résultat est celui attendu
        $this->assertEquals(1, $result);
    }

    /**
     * Test de la méthode updateStudentPassword
     */
    public function testUpdateStudentPassword()
    {
        // Créer les données de test
        $userId = 4;
        $newPassword = 'nouveauMotDePasse123';

        // Créer un mock de PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        // Configurer le mock de PDO pour qu'il retourne le mock de PDOStatement
        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        // Exécuter la méthode à tester
        $result = $this->pilotModel->updateStudentPassword($userId, $newPassword);

        // Vérifier que le résultat est celui attendu
        $this->assertTrue($result);
    }

    /**
     * Test de la méthode rateEnterprise
     */
    public function testRateEnterprise()
    {
        // Créer les données de test
        $enterpriseId = 1;
        $userId = 2;
        $rating = 4;

        // Créer des mocks pour PDOStatement
        $checkStmtMock = $this->createMock(PDOStatement::class);
        $checkStmtMock->method('execute')->willReturn(true);
        $checkStmtMock->method('fetchColumn')->willReturn(0); // Pas encore d'évaluation

        $insertStmtMock = $this->createMock(PDOStatement::class);
        $insertStmtMock->method('execute')->willReturn(true);

        // Configurer le mock de PDO pour qu'il retourne les mocks de PDOStatement
        $this->pdoMock->method('prepare')
            ->will($this->onConsecutiveCalls($checkStmtMock, $insertStmtMock));

        // Exécuter la méthode à tester
        $result = $this->pilotModel->rateEnterprise($enterpriseId, $userId, $rating);

        // Vérifier que le résultat est celui attendu
        $this->assertTrue($result);
    }

    /**
     * Test de la méthode searchOffers
     */
    public function testSearchOffers()
    {
        // Créer les données de test
        $searchTerm = 'développement';
        $expectedOffers = [
            [
                'Id_Offre' => 1,
                'Titre_Offre' => 'Stage Développement Web Full-Stack',
                'Description_Offre' => 'Nous recherchons un stagiaire développeur web full-stack pour participer au développement de notre nouvelle plateforme e-commerce.',
                'Remuneration_Offre' => 800,
                'Niveau_Requis_Offre' => 'Bac+4/5',
                'Date_Debut_Offre' => '2025-04-01',
                'Duree_Min_Offre' => 4,
                'Duree_Max_Offre' => 6,
                'Id_Entreprise' => 3,
                'Nom_Entreprise' => 'WebSolutions'
            ]
        ];

        $offerSkills = [
            [
                'Id_Competence' => 1,
                'Nom_Competence' => 'PHP'
            ],
            [
                'Id_Competence' => 2,
                'Nom_Competence' => 'JavaScript'
            ],
            [
                'Id_Competence' => 3,
                'Nom_Competence' => 'React'
            ]
        ];

        // Créer des mocks pour PDOStatement
        $offersStmtMock = $this->createMock(PDOStatement::class);
        $offersStmtMock->method('execute')->willReturn(true);
        $offersStmtMock->method('fetchAll')->willReturn($expectedOffers);

        $skillsStmtMock = $this->createMock(PDOStatement::class);
        $skillsStmtMock->method('execute')->willReturn(true);
        $skillsStmtMock->method('fetchAll')->willReturn($offerSkills);

        // Configurer le mock de PDO pour qu'il retourne les mocks de PDOStatement
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($offersStmtMock, $skillsStmtMock) {
                return strpos($query, 'Competence') !== false ? $skillsStmtMock : $offersStmtMock;
            }));

        // Exécuter la méthode à tester
        $result = $this->pilotModel->searchOffers($searchTerm);

        // Vérifier que le résultat contient les offres
        $this->assertCount(1, $result);
        $this->assertEquals('Stage Développement Web Full-Stack', $result[0]['Titre_Offre']);

        // Vérifier que les compétences ont été récupérées
        $this->assertArrayHasKey('skills', $result[0]);
        $this->assertEquals($offerSkills, $result[0]['skills']);
    }

    /**
     * Test de la méthode searchEnterprises
     */
    public function testSearchEnterprises()
    {
        // Créer les données de test
        $searchTerm = 'Web';
        $expectedEnterprises = [
            [
                'Id_Entreprise' => 3,
                'Nom_Entreprise' => 'WebSolutions',
                'Description_Entreprise' => 'Agence web spécialisée dans le développement d\'applications web et mobiles',
                'rating' => 4.5,
                'offer_count' => 2
            ]
        ];

        $enterpriseSectors = [
            [
                'Id_Secteur' => 1,
                'Nom_Secteur' => 'Développement Web'
            ],
            [
                'Id_Secteur' => 2,
                'Nom_Secteur' => 'Développement Mobile'
            ]
        ];

        // Créer des mocks pour PDOStatement
        $enterprisesStmtMock = $this->createMock(PDOStatement::class);
        $enterprisesStmtMock->method('execute')->willReturn(true);
        $enterprisesStmtMock->method('fetchAll')->willReturn($expectedEnterprises);

        $sectorsStmtMock = $this->createMock(PDOStatement::class);
        $sectorsStmtMock->method('execute')->willReturn(true);
        $sectorsStmtMock->method('fetchAll')->willReturn($enterpriseSectors);

        // Configurer le mock de PDO pour qu'il retourne les mocks de PDOStatement
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($enterprisesStmtMock, $sectorsStmtMock) {
                return strpos($query, 'Secteur') !== false ? $sectorsStmtMock : $enterprisesStmtMock;
            }));

        // Exécuter la méthode à tester
        $result = $this->pilotModel->searchEnterprises($searchTerm);

        // Vérifier que le résultat contient les entreprises
        $this->assertCount(1, $result);
        $this->assertEquals('WebSolutions', $result[0]['Nom_Entreprise']);

        // Vérifier que les secteurs ont été récupérés
        $this->assertArrayHasKey('sectors', $result[0]);
        $this->assertEquals($enterpriseSectors, $result[0]['sectors']);
    }

    /**
     * Test de la méthode getEnterpriseOffers
     */
    public function testGetEnterpriseOffers()
    {
        // Créer les données de test
        $enterpriseId = 3;
        $expectedOffers = [
            [
                'Id_Offre' => 1,
                'Titre_Offre' => 'Stage Développement Web Full-Stack',
                'Description_Offre' => 'Nous recherchons un stagiaire développeur web full-stack pour participer au développement de notre nouvelle plateforme e-commerce.',
                'Remuneration_Offre' => 800,
                'Niveau_Requis_Offre' => 'Bac+4/5',
                'Date_Debut_Offre' => '2025-04-01',
                'Duree_Min_Offre' => 4,
                'Duree_Max_Offre' => 6
            ]
        ];

        $offerSkills = [
            [
                'Id_Competence' => 1,
                'Nom_Competence' => 'PHP'
            ],
            [
                'Id_Competence' => 2,
                'Nom_Competence' => 'JavaScript'
            ],
            [
                'Id_Competence' => 3,
                'Nom_Competence' => 'React'
            ]
        ];

        // Créer des mocks pour PDOStatement
        $offersStmtMock = $this->createMock(PDOStatement::class);
        $offersStmtMock->method('execute')->willReturn(true);
        $offersStmtMock->method('fetchAll')->willReturn($expectedOffers);

        $skillsStmtMock = $this->createMock(PDOStatement::class);
        $skillsStmtMock->method('execute')->willReturn(true);
        $skillsStmtMock->method('fetchAll')->willReturn($offerSkills);

        // Configurer le mock de PDO pour qu'il retourne les mocks de PDOStatement
        $this->pdoMock->method('prepare')
            ->will($this->returnCallback(function($query) use ($offersStmtMock, $skillsStmtMock) {
                return strpos($query, 'Competence') !== false ? $skillsStmtMock : $offersStmtMock;
            }));

        // Exécuter la méthode à tester
        $result = $this->pilotModel->getEnterpriseOffers($enterpriseId);

        // Vérifier que le résultat contient les offres
        $this->assertCount(1, $result);
        $this->assertEquals('Stage Développement Web Full-Stack', $result[0]['Titre_Offre']);

        // Vérifier que les compétences ont été récupérées
        $this->assertArrayHasKey('skills', $result[0]);
        $this->assertEquals($offerSkills, $result[0]['skills']);
    }

    /**
     * Test de la méthode assignPromotion
     */
    public function testAssignPromotion()
    {
        // Créer les données de test
        $pilotId = 1;
        $promotionId = 3;
        $startDate = '2025-09-01';
        $endDate = '2027-07-31';

        // Créer des mocks pour PDOStatement
        $checkStmtMock = $this->createMock(PDOStatement::class);
        $checkStmtMock->method('execute')->willReturn(true);
        $checkStmtMock->method('fetchColumn')->willReturn(0); // Pas encore d'assignation

        $insertStmtMock = $this->createMock(PDOStatement::class);
        $insertStmtMock->method('execute')->willReturn(true);

        // Configurer le mock de PDO pour qu'il retourne les mocks de PDOStatement
        $this->pdoMock->method('prepare')
            ->will($this->onConsecutiveCalls($checkStmtMock, $insertStmtMock));

        // Exécuter la méthode à tester
        $result = $this->pilotModel->assignPromotion($pilotId, $promotionId, $startDate, $endDate);

        // Vérifier que le résultat est celui attendu
        $this->assertTrue($result);
    }
}
