<?php
namespace App\Controllers;

use App\Models\PilotModel;
use App\Models\StudentModel;
use App\Models\EnterpriseModel;
use App\Models\OfferModel;

class PilotesController extends BaseController {
    private $pilotModel;
    private $studentModel;
    private $enterpriseModel;
    private $offerModel;

    public function __construct($twig, $db) {
        parent::__construct($twig, $db);
        $this->pilotModel = new PilotModel($db);
        $this->studentModel = new StudentModel($db);
        $this->enterpriseModel = new EnterpriseModel($db);
        $this->offerModel = new OfferModel($db);
    }

    public function index() {
        $this->requirePilote();
        $this->render('pilotes/index.html.twig');
    }

    // Gestion des étudiants
    public function etudiants() {
        $this->requirePilote();

        // Récupérer le pilote actuel
        $userId = $_SESSION['user_id'];
        $pilotId = $this->pilotModel->getPilotIdFromUserId($userId);

        // Récupérer les étudiants supervisés par ce pilote
        $students = $this->pilotModel->getSupervisedStudents($pilotId);

        $this->render('pilotes/etudiants/index.html.twig', [
            'pilotePage' => true,
            'students' => $students
        ]);
    }

    public function etudiantDetails($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        $this->render('pilotes/etudiants/show.html.twig', [
            'pilotePage' => true,
            'student' => $student
        ]);
    }

    public function ajouterEtudiant() {
        $this->requirePilote();
        // Afficher le formulaire d'ajout d'étudiant
        $this->render('pilotes/etudiants/ajouter.html.twig', [
            'pilotePage' => true
        ]);
    }

    public function enregistrerEtudiant() {
        $this->requirePilote();
        // Traiter le formulaire d'ajout d'étudiant
        // Code pour ajouter un étudiant...

        $this->addFlashMessage('success', 'Étudiant ajouté avec succès');
        header('Location: /pilotes/etudiants');
    }

    public function modifierEtudiant($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les compétences de l'étudiant
        $student['skills'] = $this->studentModel->getStudentSkills($etudiantId);

        $this->render('pilotes/etudiants/modifier.html.twig', [
            'pilotePage' => true,
            'student' => $student
        ]);
    }

    public function mettreAJourEtudiant($params) {
        $this->requirePilote();
        // Traiter le formulaire de modification d'étudiant
        // Code pour mettre à jour l'étudiant...

        $this->addFlashMessage('success', 'Étudiant mis à jour avec succès');
        header('Location: /pilotes/etudiants/' . $params['id']);
    }

    public function supprimerEtudiant($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Supprimer l'étudiant
        // Code pour supprimer l'étudiant...

        $this->addFlashMessage('success', 'Étudiant supprimé avec succès');
        header('Location: /pilotes/etudiants');
    }

    public function etudiantWishlist($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer la wishlist de l'étudiant
        $wishlist = $this->studentModel->getStudentWishlist($etudiantId);

        $this->render('pilotes/etudiants/wishlist.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'offers' => $wishlist
        ]);
    }

    public function etudiantOffres($params) {
        $this->requirePilote();

        $etudiantId = $params['id'] ?? null;

        if (!$etudiantId) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les détails de l'étudiant
        $student = $this->studentModel->getStudentInfo($etudiantId);

        if (!$student) {
            $this->addFlashMessage('error', 'Étudiant non trouvé');
            header('Location: /pilotes/etudiants');
            return;
        }

        // Récupérer les candidatures de l'étudiant
        $applications = $this->studentModel->getStudentApplications($etudiantId);

        $this->render('pilotes/etudiants/offres.html.twig', [
            'pilotePage' => true,
            'student' => $student,
            'offers' => $applications
        ]);
    }

    // Gestion des entreprises
    public function entreprises() {
        $this->requirePilote();

        // Récupérer les entreprises
        $enterprises = $this->enterpriseModel->getEnterprisesByName();

        $this->render('pilotes/entreprises/index.html.twig', [
            'pilotePage' => true,
            'enterprises' => $enterprises
        ]);
    }

    public function entrepriseDetails($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        $this->render('pilotes/entreprises/show.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise
        ]);
    }

    // Méthodes pour ajouter, modifier et supprimer des entreprises
    public function ajouterEntreprise() {
        $this->requirePilote();
        $this->render('pilotes/entreprises/ajouter.html.twig', [
            'pilotePage' => true
        ]);
    }

    public function enregistrerEntreprise() {
        $this->requirePilote();
        // Traiter le formulaire d'ajout d'entreprise
        // ...

        $this->addFlashMessage('success', 'Entreprise ajoutée avec succès');
        header('Location: /pilotes/entreprises');
    }

    public function modifierEntreprise($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Récupérer les détails de l'entreprise
        $enterprise = $this->enterpriseModel->getEnterpriseDetails($enterpriseId);

        if (!$enterprise) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        $this->render('pilotes/entreprises/modifier.html.twig', [
            'pilotePage' => true,
            'enterprise' => $enterprise
        ]);
    }

    public function mettreAJourEntreprise($params) {
        $this->requirePilote();
        // Traiter le formulaire de modification d'entreprise
        // ...

        $this->addFlashMessage('success', 'Entreprise mise à jour avec succès');
        header('Location: /pilotes/entreprises/' . $params['id']);
    }

    public function supprimerEntreprise($params) {
        $this->requirePilote();

        $enterpriseId = $params['id'] ?? null;

        if (!$enterpriseId) {
            $this->addFlashMessage('error', 'Entreprise non trouvée');
            header('Location: /pilotes/entreprises');
            return;
        }

        // Supprimer l'entreprise
        // ...

        $this->addFlashMessage('success', 'Entreprise supprimée avec succès');
        header('Location: /pilotes/entreprises');
    }

    // Gestion des offres
    public function offres() {
        $this->requirePilote();

        // Récupérer les offres
        $offers = $this->offerModel->getRecentOffers();

        $this->render('pilotes/offres/index.html.twig', [
            'pilotePage' => true,
            'offers' => $offers
        ]);
    }

    public function offreDetails($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupérer les détails de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        $this->render('pilotes/offres/show.html.twig', [
            'pilotePage' => true,
            'offer' => $offer
        ]);
    }

    // Méthodes pour ajouter, modifier et supprimer des offres
    public function ajouterOffre() {
        $this->requirePilote();

        // Récupérer la liste des entreprises pour le formulaire
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');

        $this->render('pilotes/offres/ajouter.html.twig', [
            'pilotePage' => true,
            'enterprises' => $enterprises
        ]);
    }

    public function enregistrerOffre() {
        $this->requirePilote();
        // Traiter le formulaire d'ajout d'offre
        // ...

        $this->addFlashMessage('success', 'Offre ajoutée avec succès');
        header('Location: /pilotes/offres');
    }

    public function modifierOffre($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupérer les détails de l'offre
        $offer = $this->offerModel->getOfferDetails($offerId);

        if (!$offer) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Récupérer la liste des entreprises pour le formulaire
        $enterprises = $this->enterpriseModel->getAll('Nom_Entreprise');

        $this->render('pilotes/offres/modifier.html.twig', [
            'pilotePage' => true,
            'offer' => $offer,
            'enterprises' => $enterprises
        ]);
    }

    public function mettreAJourOffre($params) {
        $this->requirePilote();
        // Traiter le formulaire de modification d'offre
        // ...

        $this->addFlashMessage('success', 'Offre mise à jour avec succès');
        header('Location: /pilotes/offres/' . $params['id']);
    }

    public function supprimerOffre($params) {
        $this->requirePilote();

        $offerId = $params['id'] ?? null;

        if (!$offerId) {
            $this->addFlashMessage('error', 'Offre non trouvée');
            header('Location: /pilotes/offres');
            return;
        }

        // Supprimer l'offre
        // ...

        $this->addFlashMessage('success', 'Offre supprimée avec succès');
        header('Location: /pilotes/offres');
    }
}