<?php

declare(strict_types=1);

/*
 * This file is part of Snowtricks
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TrickSubscriber implements EventSubscriberInterface
{    
    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->setDateTimeTrick($args);
        $this->setSlugTrick($args);
        $this->reformatURLVideos($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->setUpdatedAtTrick($args);
        $this->setSlugTrick($args);
        $this->reformatURLVideos($args);
    }

    /**
     * setDateTimeTrick
     * Set both datetime in trick before Persit in DB.
     */
    private function setDateTimeTrick(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Trick) {
            return;
        }

        $entity->setCreatedAt(new \DateTime());
        $entity->setUpdatedAt(new \DateTime());
    }

    /**
     * setUpdatedAtTrick.
     */
    private function setUpdatedAtTrick(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Trick) {
            return;
        }

        $entity->setUpdatedAt(new \DateTime());
    }

    /**
     * setSlugTrick
     * Call AsciiSlugger then slug the title of the trick.
     *
     * @param mixed $args
     */
    private function setSlugTrick(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Trick) {
            return;
        }

        $slugTrickName = new AsciiSlugger();
        $slugName = $slugTrickName->slug($entity->getName());        
        $entity->setSlug((string)$slugName);
    }

    /**
     * reformatURLVideos
     * Use Regex expression to get id's video from youtube then recreate a create URL with EMBED.
     *
     * @param mixed $args
     */
    private function reformatURLVideos(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Trick) {
            return;
        }

        $pattern = '%^ (?:https?://)? (?:www\.)? (?: youtu\.be/ | youtube\.com (?: /embed/ | /v/ | /watch\?v= ) ) ([\w-]{10,12}) $%x';
        foreach ($entity->getVideos() as $video) {
            // matches[1] return code, 0 return full url

            preg_match($pattern, $video->getName(), $matches);

            $id = $matches[1] ?? ''; // condition necessary bug undefinied array Key
            $newUrl = 'https://www.youtube.com/embed/'.$id;
            $video->setName($newUrl);
        }
    }
}
