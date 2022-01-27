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
        $entity = $args->getEntity();
        if (!$entity instanceof Participant) {
            return;
        }
        $object = $args->getObject(); //ici je récupère l'objet

        $linkForImage = $this->uploaderHelper->asset($object, 'imageFile'); //ici je construit le chemin de l'ancien fichier à supprimer

        if ($linkForImage == null) {
            return;
        }
        if ($linkForImage != null) {
            // je commente car j'ai l'erreur suivante:
            // Warning: unlink(./img/profils/61f2b920d01f2681179437.jpg): No such file or directory
            // j'ai essayer de mettre en try catch mais j'ai l'erreur quand même
            /*
            try {
                unlink('.' . $linkForImage); //ici je supprime le fichier
            }
            catch (Exception $e)
            {
                //@TODO
            }
            */
        }

        if ($entity->getImageFile() instanceof UploadedFile) {
            $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
        }
    }
}
