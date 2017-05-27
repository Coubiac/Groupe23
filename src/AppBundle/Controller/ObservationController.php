<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use AppBundle\Entity\Observation;
use AppBundle\Entity\Taxref;
use AppBundle\Form\ObservationType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ObservationController extends BaseController
{



    /**
     * @Route("/addObservation", name="addObservation")
     * @Security("has_role('ROLE_USER')")
     * @Method({"GET", "POST"})
     */
    public function addObservationAction(Request $request)
    {
        // Création de l'entité Observation
        $observation = new Observation();
        $observation->setDate(new \Datetime);


        $observation->setUser($this->get('security.token_storage')->getToken()->getUser());

        $form = $this->createForm(ObservationType::class, $observation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $observation->getImage();

            // Generate a unique name for the file before saving it
            $fileName = $this->get('app.image_uploader')->upload($file);

            $observation->setImage($fileName);


            // On récupère l'EntityManager
            $em = $this->getDoctrine()->getManager();
            // Étape 1 : On « persiste » l'entité
            $em->persist($observation);
            // Étape 2 : On « flush » tout ce qui a été persisté avant
            $em->flush();

            return $this->redirect($this->generateUrl('viewObservation', array('id' => $observation->getId())));
        }

        return $this->render('default/addObservation.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/viewObservation/{id}", name="viewObservation")
     * @Method({"GET", "POST"})
     */
    public function viewObservationAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $observation = $em->getRepository('AppBundle:Observation')->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id n'existe pas, d'où ce if :
        if (null === $observation) {
            throw new NotFoundHttpException("L'observation d'id ".$id." n'existe pas.");
        }



        return $this->render('default/viewObservation.html.twig', array(
            'observation'           => $observation,
        ));
    }


    /**
     * @Route("/exportTotal", name="exportTotal")
     * @Method({"GET", "POST"})
     *
     */
    public function exportAllObservations()
    {
        //on récupère toutes nos observations
        $observations = $this->getDoctrine()->getManager()->getRepository('AppBundle:Observation')->findAll();

        // On appel de service de création de fichier Excel
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        // On définit les propriétés globales du document
        $phpExcelObject->getProperties()->setCreator("Michel Dujardin")
            ->setLastModifiedBy("Giulio De Donato")
            ->setTitle("Liste des observations")
            ->setSubject("Observations d'oiseaux")
            ->setDescription("Liste de toutes les observations d'oiseau recencées par l'assosiation NAO")
            ->setKeywords("oiseaux nao taxref")
            ->setCategory("data export");

        // On prépare le titre des colonnes
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'id')
            ->setCellValue('B1', 'Date')
            ->setCellValue('C1', 'Auteur')
            ->setCellValue('D1', 'Latitude')
            ->setCellValue('E1', 'Longitude')
            ->setCellValue('F1', 'Commentaire')
            ->setCellValue('G1', 'CDNom')
            ->setCellValue('H1', 'LbNom')
            ->setCellValue('I1', 'Nom Vern')
        ;

        //ensuite on boucle pour remplir le tableau excel avec nos observations
        $i = 2;
        foreach ($observations as $observation)
        {
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.$i, $observation->getId())
                ->setCellValue('B'.$i,$observation->getDate())
                ->setCellValue('C'.$i,$observation->getUser()->getUsername())
                ->setCellValue('D'.$i,$observation->getLatitude())
                ->setCellValue('E'.$i,$observation->getLongitude())
                ->setCellValue('F'.$i,$observation->getComment())
                ->setCellValue('G'.$i,$observation->getTaxref()->getCdnom())
                ->setCellValue('H'.$i,$observation->getTaxref()->getLbnom())
                ->setCellValue('I'.$i,$observation->getTaxref()->getNonvern());
            $i = $i + 1;
        }

        // On nomme l'onglet Actif
        $phpExcelObject->getActiveSheet()->setTitle('Export');

        // On précise quel onglet doit être ouvert lors de l'ouverture du fichier
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'liste-observations.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }





}
