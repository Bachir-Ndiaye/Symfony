<?php

// src/Controller/ProgramController.php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
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
        $programs = $this->getDoctrine()
                         ->getRepository(Program::class)
                         ->findAll();

        return $this->render('program/index.html.twig', [
            'programs' => $programs
         ]);
    }

    /**
     * @Route("/show/{id<^[0-9]+$>}", name="show")
     */
    public function show(int $id): Response
    {
        $program = $this->getDoctrine()
                         ->getRepository(Program::class)
                         ->findOneBy([
                            'id' => $id
                         ]);

        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy([
                'program' => $id
            ]);

        if (!$program) {
        throw $this->createNotFoundException(
            'No program with id : '.$id.' found in program\'s table.'
        );
    }
    return $this->render('program/show.html.twig', [
        'program' => $program, 
        'seasons' => $seasons

    ]);
    }

    /**
     * @Route("/show/{programId<^[0-9]+$>}/season/{seasonId<^[0-9]+$>}", name="season_show")
     */
    public function showSeason(int $programId, int $seasonId): Response
    {
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy([
                'id' => $seasonId
            ]);

            

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy([
                'id' => $programId
            ]);

        $episodes = $this->getDoctrine()
        ->getRepository(Episode::class)
        ->findBy([
            'season' => $seasonId
        ]); 
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes
        ]);
    }
}
