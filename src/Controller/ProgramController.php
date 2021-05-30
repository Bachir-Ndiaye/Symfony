<?php

// src/Controller/ProgramController.php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**

* @Route("/programs", name="program_")

*/
class ProgramController extends AbstractController

{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {

        return $this->render('program/index.html.twig', [
            'website' => 'Wild Séries',
         ]);
    }

    /**
     * @Route("/{id}", name="show", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function show(int $id): Response
    {
        if(is_int($id)){
            return $this->render('program/show.html.twig',[
                'id' => $id
            ]);
        }else{
            return $this->redirectToRoute('status/notfound.html.twig', [], 404);
        }
        
    }
}
