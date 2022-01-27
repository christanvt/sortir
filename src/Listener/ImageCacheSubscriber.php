<?php

namespace App\Listener;

use App\Entity\Participant;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageCacheSubscriber implements EventSubscriber
{
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;
    public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper)
    {
        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getSubscribedEvents()
    {
        return [
            'preRemove',
            'preUpdate'
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Participant) {
            return;
        }
        $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
    }
    public function preUpdate(PreUpdateEventArgs $args)
    {


        $object = $args->getObject(); //ici je récupère l'objet

        $linkForImage = $this->uploaderHelper->asset($object, 'imageFile'); //ici je construit le chemin de l'ancien fichier à supprimer
        dump($linkForImage);
        if ($linkForImage == null) {
            return;
        }
        if ($linkForImage != null) {
            unlink('.' . $linkForImage); //ici je supprime le fichier 
        }








        $entity = $args->getEntity();
        if (!$entity instanceof Participant) {
            return;
        }
        if ($entity->getImageFile() instanceof UploadedFile) {
            $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
        }
    }
}
