<?php
// Controlador del panel principal

require_once __DIR__ . '/../core/Controller.php';

class DashboardController extends Controller {

    public function index(): void {
        $this->checkAuth();
        $this->view("dashboard");
    }
}
