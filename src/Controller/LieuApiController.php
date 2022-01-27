<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des lieux
 *
 * Class LieuApiController
 * @package App\Controller
 * @Route("/api/lieu", name="lieu_")
 */
class LieuApiController extends AbstractController
{
    /**
     * Méthode appelée en AJAX seulement. Crée un nouveau lieu.
     * Voir templates/sortie/create.html.twig pour le code JS !
     *
     * @Route("/create", name="create")
     */
    public function create(Request $request, EntityManagerInterface $em)/*, MapBoxHelper $mapBoxHelper)*/
    {
        //récupère les données POST
        $lieuData = $request->request->get('lieu');

        //récupère les infos de la ville associée à ce lieu
        $villeRepo = $em->getRepository(Ville::class);
        $ville = $villeRepo->find($lieuData["ville"]);

        //@TODO: gérer si on ne trouve pas la ville

        //instancie notre Location et l'hydrate avec les données reçues
        $lieu = new Lieu();
        $lieu->setNom($lieuData["nom"]);
        $lieu->setRue($lieuData["rue"]);
        $lieu->setVille($ville);
        $lieu->getVille()->setCodePostal($lieuData["codePostal"]);

        //récupère les coordonnées du lieu grâce à MapBox
        //on appelle ici un service créé dans src/Geolocation/, afin de limiter la quantité de code dans le Controller
        //et afin d'organiser correctement notre code
        /*
        $coordinates = $mapBoxHelper->getAddressCoordinates($lieu->getRue(),
            $lieu->getVille()->getCodePostal(),
            $ville->getNom());

        //hydrate les coordonnées reçues dans l'entité
        if (!empty($coordinates)){
            $lieu->setLatitude($coordinates['lat']);
            $lieu->setLongitude($coordinates['lng']);
        }
        */

        //sauvegarde en bdd
        $em->persist($lieu);
        $em->flush();

        //les données à renvoyer au code JS
        //status est arbitraire... mais je prend pour acquis que je renverrais toujours cette clé
        //avec comme valeur soit "ok", soit "error", pour aider le traitement côté client
        //je renvois aussi la Location. Pour que ça marche, j'ai implémenté \JsonSerializable dans l'entité, sinon c'est vide
        $data = [
            "status" => "ok",
            "lieu" => $lieu
        ];

        //renvoie la réponse sous forme de données JSON
        //le bon Content-Type est automatiquement configuré par cet objet JsonResponse
        return new JsonResponse($data);
    }

    /**
     * Méthode appelée en AJAX seulement. Retourne la liste des villes correspondant à un code postal.
     * @Route("/villes/search", name="find_villes_by_cp")
     */
    public function findVillesByCodePostal(Request $request, VilleRepository $villeRepo)
    {
        $cp= $request->query->get('codePostal');
        $villes = '';

        if(strlen($cp) == 5) {
            $villes = $villeRepo->findBy(['codePostal' => $cp], ['nom' => 'ASC']);
        }
        else if ($cp == '0'){
            $villes = $villeRepo->findAll();
        }

        return $this->render('lieu/ajax_villes_list.html.twig', ['villes' => $villes]);
    }
}
