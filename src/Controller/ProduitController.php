<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/{_locale}/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit')]
    public function index(EntityManagerInterface $em, Request $r): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible d\'ajouter l\'image');
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $produit->setImage($newFilename);
            }

            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté');
        }

        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'ajout' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'produit')]
    public function show(Produit $produit = null, Request $r, EntityManagerInterface $em)
    {
        if ($produit == null) {
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('app_produit');
        }

        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit mis à jour');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'edit' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'produit_delete')]
    public function delete(Produit $produit = null, EntityManagerInterface $em)
    {
        if ($produit == null) {
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('app_produit');
        }

        $em->remove($produit);
        $em->flush();

        $this->addFlash('warning', 'Produit supprimé');
        return $this->redirectToRoute('app_produit');
    }
}
