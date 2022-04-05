<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Message;
use App\Repository\CustomerRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/panel", name="adminPanel")
     * affichage de tous les customers
     */
    public function index(CustomerRepository $repo, MessageRepository $repo_message): Response
    {
        $customer = $repo->findBy([], ["id" => "DESC"]);

        return $this->render('admin/panel.html.twig', [
            "customer" => $customer,
        ]);
    }

    /**
     * @Route("/admin/customer/{id}", name="showCustomer")
     * Page de détail d'un customer contenant ces différents messages
     */
    public function showCustomer(Customer $customer, MessageRepository $repo){

        $message = $repo->findBy(["customer" => $customer], ["createdAt" => "DESC"]);

        return $this->render('admin/showCustomer.html.twig', [
            "customer" => $customer,
            "message" => $message
        ]);
    }

    /**
     * @Route("/admin/message/{id}", name="showMessage")
     * Affichage d'un message
     */
    public function showMessage(Message $message, EntityManagerInterface $manager){

        $vu = $message->getVu();

        if($vu == 1){
            $message->setVu(0);
            $manager->persist($message);
            $manager->flush();
        }

        return $this->render('admin/showMessage.html.twig', [
            "message" => $message
        ]);
    }

    /**
     * @Route("/admin/message/delete/{id}", name="deleteMessage")
     * Suppression d'un message.
     */
    public function deleteMessage(Message $message, EntityManagerInterface $manager, Request $req){

        if ($this->isCsrfTokenValid("SUP".$message->getId(),$req->get('_token'))) {
            $manager->remove($message);
            $manager->flush();
            $this->addFlash("success", "suppréssion éffectuer");
            return $this->redirectToRoute('adminPanel');
        }
        $this->addFlash('success','Modification éffectué');
        return $this->redirectToRoute('admin_actu');
    }

    /**
     * @Route("/admin/customer/delete/{id}", name="deleteCustomer")
     * Suppression d'un customer. 
     */
    public function deleteCustomer(Customer $customer, EntityManagerInterface $manager, Request $req){

        $messages = $customer->getMessage();

        if ($this->isCsrfTokenValid("SUP".$customer->getId(),$req->get('_token'))) {
            foreach($messages as $message){
                $manager->remove($message);
            }
            $manager->remove($customer);
            $manager->flush();
            $this->addFlash("success", "suppréssion éffectuer");
            return $this->redirectToRoute('adminPanel');
        }
        $this->addFlash('success','Modification éffectué');
        return $this->redirectToRoute('admin_actu');
    }
}
