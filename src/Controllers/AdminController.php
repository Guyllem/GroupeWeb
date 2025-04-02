<?php
namespace App\Controllers;

use App\Models\PilotModel;
use App\Models\StudentModel;
use App\Models\EnterpriseModel;
use App\Models\OfferModel;
use App\Models\UserModel;

class AdminController extends BaseController {
    private $pilotModel;
    private $studentModel;
    private $enterpriseModel;
    private $offerModel;
    private $userModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->pilotModel = new PilotModel($db);
        $this->studentModel = new StudentModel($db);
        $this->enterpriseModel = new EnterpriseModel($db);
        $this->offerModel = new OfferModel($db);
        $this->userModel = new UserModel($db);
    }

    public function index() {
        $this->requireAdmin();
        $this->render('admin/index.html.twig', [
            'adminPage' => true
        ]);
    }

    // Gestion des pilotes
    public function pilotes() {
        $this->requireAdmin();

        // Récupérer les pilotes
        $pilots = $this->pilotModel->getPilotsByName();

        $this->render('admin/pilotes/index.html.twig', [
            'adminPage' => true,
            'pilots' => $pilots
        ]);
    }

    public function piloteDetails($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupérer les détails du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }


        $this->render('admin/pilotes/show.html.twig', [
            'adminPage' => true,
            'pilot' => $pilot
        ]);
    }

    public function ajouterPilote() {
        $this->requireAdmin();
        $this->render('admin/pilotes/ajouter.html.twig', [
            'adminPage' => true
        ]);
    }

    public function enregistrerPilote() {
        $this->requireAdmin();

        // Récupérer les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';

        // Vérifier si l'email existe déjà
        // ...

        // Créer l'utilisateur avec le rôle pilote
        $userId = $this->userModel->createUser($email, $password, $nom, $prenom, 'pilote');

        if ($userId) {
            $this->addFlashMessage('success', 'Pilote ajouté avec succès');
        } else {
            $this->addFlashMessage('error', 'Erreur lors de l\'ajout du pilote');
        }

        header('Location: /admin/pilotes');
    }

    public function modifierPilote($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Récupérer les détails du pilote
        $pilot = $this->pilotModel->getPilotDetails($piloteId);

        if (!$pilot) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        $this->render('admin/pilotes/modifier.html.twig', [
            'adminPage' => true,
            'pilot' => $pilot
        ]);
    }

    public function mettreAJourPilote($params) {
        $this->requireAdmin();

        // Traiter le formulaire de modification du pilote
        // ...

        $this->addFlashMessage('success', 'Pilote mis à jour avec succès');
        header('Location: /admin/pilotes/' . $params['id']);
    }

    public function supprimerPilote($params) {
        $this->requireAdmin();

        $piloteId = $params['id'] ?? null;

        if (!$piloteId) {
            $this->addFlashMessage('error', 'Pilote non trouvé');
            header('Location: /admin/pilotes');
            return;
        }

        // Supprimer le pilote
        // ...

        $this->addFlashMessage('success', 'Pilote supprimé avec succès');
        header('Location: /admin/pilotes');
    }

    // Gestion des étudiants (méthodes similaires à PilotesController)
    public function etudiants() {
        $this->requireAdmin();

        // Récupérer tous les étudiants
        $students = $this->studentModel->getAllStudents();

        $this->render('pilotes/etudiants/index.html.twig', [
            'adminPage' => true,
            'students' => $students
        ]);
    }

    // Répéter les méthodes pour la gestion des étudiants comme dans PilotesController
    // ...

    // Gestion des entreprises (méthodes similaires à PilotesController)
    public function entreprises() {
        $this->requireAdmin();

        $enterprises = $this->enterpriseModel->getEnterprisesByName();

        $this->render('pilotes/entreprises/index.html.twig', [
            'adminPage' => true,
            'enterprises' => $enterprises
        ]);
    }

    // Répéter les méthodes pour la gestion des entreprises comme dans PilotesController
    // ...

    // Gestion des offres (méthodes similaires à PilotesController)
    public function offres() {
        $this->requireAdmin();

        $offers = $this->offerModel->getRecentOffers();

        $this->render('pilotes/offres/index.html.twig', [
            'adminPage' => true,
            'offers' => $offers
        ]);
    }

    // Répéter les méthodes pour la gestion des offres comme dans PilotesController
    // ...
}