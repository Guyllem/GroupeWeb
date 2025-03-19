<?php
namespace App\Controllers;

class HomeController {
    private $twig;
    private $db;

    public function __construct($twig, $db) {
        $this->twig = $twig;
        $this->db = $db;
    }

    public function index() {
        echo "Hello World! This is the home page.";
        // Or with Twig:
        // echo $this->twig->render('home/index.html.twig', [
        //     'message' => 'Hello World!'
        // ]);
    }

    public function about() {
        echo "This is the about page.";
        // Or with Twig:
        // echo $this->twig->render('home/about.html.twig');
    }
}