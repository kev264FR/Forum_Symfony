<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Breadcrumbs $bc)
    {
        $bc->addItem("Home", $this->get("router")->generate("home"));
        return $this->render('home/index.html.twig');
    }
}
