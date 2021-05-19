<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/tasks/", name="api_index", methods={"GET"})
     */
    public function index(TaskRepository $taskRepository): Response
    {
        // récupérer la liste des taches depuis la Bdd.
        $tasks = $taskRepository->findAll();

        if($tasks){
 
        // méthode avec entity en private (fonctione si entity en public aussi ):
        // $data = $this->get('serializer')->serialize($tasks, 'json');
        // $response = new Response($data);
        //

        // génére la response: 
        $response = new Response();
        
        // ajoutes les headers http a la response:
        $response->headers->set('Content-Type', 'application/json');
        // $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setStatusCode(Response::HTTP_OK);
        
        // méthode avec entity EXCLUSIVEMENT en public : 
        $response->setContent(json_encode($tasks));
        //
        
        // return la response : 
        return $response;

        }else{

        return new Response(Response::HTTP_BAD_REQUEST);

        }

    }

    /**
     * @Route("/tasks/new/", name="api_new", methods={"POST"})
     */
    public function new(Request $request)
    {
        $task = new Task();
        $content = $request->getContent();
        $data = json_decode($content);

        if(($data->name !== "") && ($data->completed  === 0 || $data->completed === 1)){
            $task->setName($data->name);
            $task->setCompleted($data->completed);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            $response = new Response();
            // $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setStatusCode(Response::HTTP_CREATED);

            return $response;
        }
        else{
            $response = new Response();
            // $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $response;
        }
    }

    /**
     * @Route("/tasks/{id}", name="api_show", methods={"GET"})
     */
    public function show(Task $task): Response
    {
        return $this->render('api/show.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="api_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(Task1Type::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('api_index');
        }

        return $this->render('api/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tasks/{id}/delete/", name="api_delete", methods={"DELETE"})
     */
    public function delete($id, TaskRepository $taskRepository): Response
    {
        $task = $taskRepository->find($id);
        $response = new Response();
        // $response->headers->set('Access-Control-Allow-Origin', '*');

        if($task){
        
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($task);
            $entityManager->flush();

            
            $response->setStatusCode(Response::HTTP_OK);

            return $response;

        }
        else{

            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            
            return $response;
        }
    }
}
