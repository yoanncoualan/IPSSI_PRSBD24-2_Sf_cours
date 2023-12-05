<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {

        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);

        // Demande d'analyser la requete HTTP
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Le formulaire a été soumis et est valide
            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie ajoutée');
        }

        // Récupération des catégories (SELECT *)
        $categories = $em->getRepository(Categorie::class)->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'ajout' => $form->createView() // Envois la version HTML du formulaire
        ]);
    }

    #[Route('/{id}', name: 'category')]
    public function category(
        Categorie $categorie = null,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Si la categorie est introuvable
        if ($categorie == null) {
            $this->addFlash('danger', 'Catégorie introuvable');
            return $this->redirectToRoute('app_category');
        }

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie mise à jour');
        }

        return $this->render('category/show.html.twig', [
            'categorie' => $categorie,
            'edit' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_category')]
    public function delete(Categorie $categorie = null, EntityManagerInterface $em)
    {
        if ($categorie == null) {
            $this->addFlash('danger', 'Catégorie introuvable');
            return $this->redirectToRoute('app_category');
        }

        $em->remove($categorie);
        $em->flush();

        $this->addFlash('warning', 'Catégorie supprimée');
        return $this->redirectToRoute('app_category');
    }
}
