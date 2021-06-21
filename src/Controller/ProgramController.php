<?php

// src/Controller/ProgramController.php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Form\SearchProgramFormType;
use App\Repository\ProgramRepository;
use App\Service\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
* @Route("/programs", name="program_")
*/
class ProgramController extends AbstractController

{
    /**
     * @Route("/", name="index")
     */
    public function index(request $request, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);
        } else {
            $programs = $programRepository->findAll();
            
        }

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView()
         ]);
    }

    /**
     * The controller for the category add form
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());

            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new Email())
                    ->from($this->getParameter('mailer_from'))
                    ->to('admin@wild-series.com')
                    ->subject('Une nouvelle série est publiée !')
                    ->html($this->renderView('program/newProgramEmail.html.twig',[
                        'program' => $program
                    ]));

            $mailer->send($email);

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     */
    public function show(Program $program): Response
    {
        $seasons = $program->getSeasons();
        if (!$program) {
        throw $this->createNotFoundException(
            'No program found in program\'s table.'
        );
    }
    return $this->render('program/show.html.twig', [
        'program' => $program,
        'seasons' => $seasons
        ]);
    }

    /**
     * @Route("/show/{programId<^[0-9]+$>}/season/{seasonId<^[0-9]+$>}", name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     */
    public function showSeason(Program $program, Season $season): Response
    {
        $episodes = $season->getEpisodes();

        if (!$season) {
            throw $this->createNotFoundException(
                'No season found in season\'s table.'
            );
        }
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes
        ]);
    }

    /**
     * @Route("/programs/{programId<^[0-9]+$>}/seasons/{seasonId<^[0-9]+$>}/episodes/{episodeId<^[0-9]+$>}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeId": "id"}})
     */
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request): Response
    {
       
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $comment->setAuthor($this->getUser());
            $comment->setEpisode($episode);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->render('program/episode_show.html.twig', [
                'program' => $program,
                'season' => $season,
                'episode' => $episode,
                'form' => $form->createView()
            ]);
        }

        if (!$episode) {
            throw $this->createNotFoundException(
                'No episode found in season\'s table.'
            );
        }
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Program $program, Request $request): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        // Check wether the logged in user is the owner of the program
        if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Seul le proprio de cette série peut le modifier');
        }

        if($form->isSubmitted() && $form->isValid())
        {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{commentId}", name="comment_delete", methods={"POST"})
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function delete(Request $request, Comment $comment): Response
    {
        $program = $comment->getEpisode()->getSeason()->getProgram()->getId();
        $season = $comment->getEpisode()->getSeason()->getId();
        $episode = $comment->getEpisode()->getId();

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('program_episode_show', [
            'programId' => $program,
            'seasonId' => $season,
            'episodeId' => $episode
        ]);
    }

    
}
