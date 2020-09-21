<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Entity\Topic;
use App\Form\ReplyType;
use App\Form\TopicType;
use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class ForumController extends AbstractController
{
    /**
     * @Route("/categories", name="categories")
     */
    public function index(Breadcrumbs $bc)
    {
        $bc->addItem("Home", $this->get("router")->generate("home"));
        $bc->addItem("Catégories", $this->get("router")->generate("categories"));


        $categories = $this->getDoctrine()
                            ->getRepository(Categorie::class)
                            ->getAll();

        return $this->render('forum/categories.html.twig', [
            'categories' => $categories,
            // "menu"=>$menu
        ]);
    }


    /**
     * @Route("/categorie/new", name="add_categorie")
     * @IsGranted("ROLE_ADMIN")
     */
    public function addCategorie(Request $request, EntityManagerInterface $manager, Breadcrumbs $bc){

        $bc->addItem("Home", $this->get("router")->generate("home"));
        $bc->addItem("Catégories", $this->get("router")->generate("categories"));
        $bc->addItem("Ajouter une categorie", $this->get("router")->generate("add_categorie"));

        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($categorie);
            $manager->flush();

            return $this->redirectToRoute("categories");
        }

        return $this->render("forum/new_categorie.html.twig",[
            "form"=>$form->createView()
        ]);
    }

    /**
     * @Route("/categorie/{id}", name="topics_in_categorie")
     */
    public function showCategorie(Categorie $categorie, Breadcrumbs $bc){

        $bc->addItem("Home", $this->get("router")->generate("home"));
        $bc->addItem("Catégories", $this->get("router")->generate("categories"));
        $bc->addItem($categorie->getName(), $this->get("router")->generate("topics_in_categorie", ['id' => $categorie->getId()]));
       

        return $this->render("forum/topics_in_categorie.html.twig", [
            "categorie"=>$categorie
        ]);
    }

    /**
     * @Route("/topic/new/{id}", name="new_topic")
     * @IsGranted("ROLE_USER")
     */
    public function newTopic(Request $request, EntityManagerInterface $manager, Categorie $categorie, Breadcrumbs $bc){

        $bc->addItem("Home", $this->get("router")->generate("home"));
        $bc->addItem("Catégories", $this->get("router")->generate("categories"));
        $bc->addItem($categorie->getName(), $this->get("router")->generate("topics_in_categorie", ['id' => $categorie->getId()]));
        $bc->addItem("Nouveau Topic", $this->get("router")->generate("new_topic", ['id' => $categorie->getId()]));

        $post = new Post();
        $topic = new Topic();

        $form = $this->createForm(TopicType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setTitle($form->get("title")->getData());
            $topic->setLocked(false);
            $topic->setUser($this->getUser());
            $topic->setCategorie($categorie);

            $post->setText($form->get("text")->getData());
            $post->setUser($this->getUser());
            $post->setCreatedAt(new DateTime());
            $post->setTopic($topic);

            $manager->persist($topic);
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute("posts_in_topic", ["id"=>$topic->getId()]);
        }

        return $this->render("forum/new_topic.html.twig",[
            "form"=>$form->createView(),
            "categorie"=>$categorie
        ]);

    }

    /**
     * @Route("/topic/{id}", name="posts_in_topic")
     */
    public function showTopic(Request $request, EntityManagerInterface $manager, Topic $topic, Breadcrumbs $bc){

        $bc->addItem("Home", $this->get("router")->generate("home"));
        $bc->addItem("Catégories", $this->get("router")->generate("categories"));
        $bc->addItem($topic->getCategorie()->getName(), $this->get("router")->generate("topics_in_categorie", ['id' => $topic->getCategorie()->getId()]));
        $bc->addItem($topic->getTitle(), $this->get("router")->generate("posts_in_topic", ["id"=>$topic->getId()]));

        $post = new Post();

        $form = $this->createForm(ReplyType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $post->setCreatedAt(new DateTime());
            $post->setUser($this->getUser());
            $post->setTopic($topic);

            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute("posts_in_topic", ["id"=>$topic->getId()]);
        }

        return $this->render("forum/posts_in_topic.html.twig", [
            "topic"=>$topic,
            "form"=>$form->createView()
        ]);
    }

    /**
     * @Route("/post/edit/{id}", name="edit_post")
     * @IsGranted("ROLE_USER")
     */
    public function editPost(Request $request, Post $post, EntityManagerInterface $manager, Breadcrumbs $bc){

        $bc->addItem("Home", $this->get("router")->generate("home"));
        $bc->addItem("Catégories", $this->get("router")->generate("categories"));
        $bc->addItem($post->getTopic()->getCategorie()->getName(), $this->get("router")->generate("topics_in_categorie", ['id' => $post->getTopic()->getCategorie()->getId()]));
        $bc->addItem($post->getTopic()->getTitle(), $this->get("router")->generate("posts_in_topic", ["id"=>$post->getTopic()->getId()]));
        $bc->addItem("Modifier message", $this->get("router")->generate("edit_post", ["id"=>$post->getId()]));

        if ($post->getUser() == $this->getUser() || $this->getUser()->hasRole("ROLE_ADMIN")) {
            $form = $this->createForm(ReplyType::class, $post);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                $manager->persist($post);
                $manager->flush();

                return $this->redirectToRoute("posts_in_topic", ["id"=>$post->getTopic()->getId()]);
            }

            return $this->render("forum/edit_post.html.twig", [
                "form"=>$form->createView()
            ]);
        }
        $this->addFlash("error", "Vous ne pouvez pas faire ceci");
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/post/delete/{id}", name="delete_post")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deletePost(Post $post, EntityManagerInterface $manager){
        $topic = $post->getTopic();
        $manager->remove($post);
        $deletedTopic = false;

        if (count($topic->getPosts())-1 == 0 ) {
            $deletedTopic = true;
            $manager->remove($topic);
        }

        $manager->flush();

        if ($deletedTopic) {
            return $this->redirectToRoute("topics_in_categorie", ["id"=>$topic->getCategorie()->getId()]);
        }
        return $this->redirectToRoute("posts_in_topic", ["id"=>$topic->getId()]);
    }

    /**
     * @Route("/topic/delete/{id}", name="delete_topic")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteTopic(Topic $topic, EntityManagerInterface $manager){
        $catId = $topic->getCategorie()->getId();
        foreach ($topic->getPosts() as $post ){
            $manager->remove($post);
        }
        $manager->remove($topic);
        $manager->flush();

        return $this->redirectToRoute("topics_in_categorie", ["id"=>$catId]);
    }

    /**
     * @Route("/topic/lock/{id}", name="lock_topic")
     * @IsGranted("ROLE_USER")
     */
    public function lockTopic(Topic $topic, EntityManagerInterface $manager){
        $topId = $topic->getCategorie()->getId();
        if ($this->getUser() == $topic->getUser() || $this->getUser()->hasRole("ROLE_ADMIN")) {
            $topic->setLocked(true);
            $manager->persist($topic);
            $manager->flush();
        }
        return $this->redirectToRoute("topics_in_categorie", ["id"=>$topId]);
    }

    /**
     * @Route("/topic/unlock/{id}", name="unlock_topic")
     * @IsGranted("ROLE_ADMIN")
     */
    public function unlockTopic(Topic $topic, EntityManagerInterface $manager){
        $topId = $topic->getCategorie()->getId();

            $topic->setLocked(false);
            $manager->persist($topic);
            $manager->flush();

        return $this->redirectToRoute("topics_in_categorie", ["id"=>$topId]);
    }


    /**
     * @Route("/categorie/delete/{id}", name="delete_categorie")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteCategorie(Categorie $categorie, EntityManagerInterface $manager){
        $test = 0;
        foreach ($categorie->getTopics() as $topic ) {
            $test = $test + 1;
        }
        if ($test == 0) {
            $manager->remove($categorie);
            $manager->flush();
        }else{
            $this->addFlash("error", "Pour effacer une catégorie elle doit ètre vide");
        }
        
        
        return $this->redirectToRoute("categories");
    }

    
    public function test(){
        
    }
}
