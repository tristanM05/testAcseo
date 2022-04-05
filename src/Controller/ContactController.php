<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Customer;
use App\Entity\Message;
use App\Form\ContactType;
use App\Repository\CustomerRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/", name="app_contact")
     */
    public function index(MailerInterface $mailer, Request $request, EntityManagerInterface $manager, CustomerRepository $repo): Response
    {
        $contact = new Contact;
        $newMessage = new Message;

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        // Récupération des données du formulaire
        $messageContact = $contact->getMessage();
        $mail = $contact->getEmail();
        $lastname = $contact->getLastname();
        $firstname = $contact->getFirstname();
        $phone = $contact->getPhone();

        if($form->isSubmitted() && $form->isValid()){
            //initialisation de l'envoie du mail.
            $message = (new TemplatedEmail())
                ->from($mail)
                ->to('acseo@contact.fr')
                ->html("<p>
                Email: $mail<br>
                Téléphone: $phone<br>
                Nom: $lastname $firstname<br>
                Message: $messageContact
                </p>");

                $mailer->send($message);

                $existCustomer = $repo->findOneBy(["mail" => $mail]);
                $now = new DateTime("now");
                
                // condition permettant de vérifier si un customer est déja existant ou non, afin de lui créé son propre répertoire si il n'existe pas.
                if($existCustomer){
                    $newMessage->setCustomer($existCustomer);
                    $newMessage->setFistrName($firstname);
                    $newMessage->setLastName($lastname);
                    $newMessage->setMail($mail);
                    $newMessage->setPhone($phone);
                    $newMessage->setMessage($messageContact);
                    $newMessage->setVu(1);
                    $newMessage->setCreatedAt($now);

                    // création du tableau pour le fichier JSON unique.
                    $data = [];
                    $data["Nom"] = $lastname;
                    $data["Prénom"] = $firstname;
                    $data["Email"] = $mail;
                    $data["phone"] = $phone;
                    $data["message"] = $messageContact;
                    
                    $json = json_encode($data);
                    file_put_contents($mail . ".json", $json);

                    $manager->persist($newMessage);
                    $manager->flush();
                }else{
                    $customer = new Customer;
                    $customer->setMail($mail);
                    $manager->persist($customer);

                    $newMessage->setCustomer($customer);
                    $newMessage->setFistrName($firstname);
                    $newMessage->setLastName($lastname);
                    $newMessage->setMail($mail);
                    $newMessage->setPhone($phone);
                    $newMessage->setMessage($messageContact);
                    $newMessage->setVu(1);
                    $newMessage->setCreatedAt($now);

                    $data = [];
                    $data["Nom"] = $lastname;
                    $data["Prénom"] = $firstname;
                    $data["Email"] = $mail;
                    $data["phone"] = $phone;
                    $data["message"] = $messageContact;
                    
                    $json = json_encode($data);
                    file_put_contents($mail . ".json", $json);
                   

                    $manager->persist($newMessage);
                    $manager->flush();
                }

                $this->addFlash("success", "Votre message a bien été envoyé ! ");
                return $this->redirectToRoute("app_contact");
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
