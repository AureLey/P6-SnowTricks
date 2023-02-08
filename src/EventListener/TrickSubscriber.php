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
use App\Entity\Video;
use App\Entity\Comment;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class EntitySubscriber implements EventSubscriberInterface
{

    
    /**
     * getSubscribedEvents.
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::onFlush,
        ];
    }
    
    /**
     * prePersist
     *
     * @param  LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->setDateTimeTrick($args);
        $this->setSlugTrick($args);
        $this->setDateTimeComment($args);
    }
    
    /**
     * preUpdate
     *
     * @param  LifecycleEventArgs $args
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->setUpdatedAtTrick($args);
        $this->setSlugTrick($args);
    }
    
    /**
     * onFlush
     *
     * @param  OnFlushEventArgs $eventArgs
     * @return void
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        // Get entityManager from EventArgs.
        $em = $eventArgs->getObjectManager();

        // Call getUnitOfWork method to modify the collection of images.
        $uow = $em->getUnitOfWork();

        // Method return all collection modified.
        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            // Pattern to rewrite youtube URL with Embed.
            $pattern = '%^ (?:https?://)? (?:www\.)? (?: youtu\.be/ | youtube\.com (?: /embed/ | /v/ | /watch\?v= ) ) ([\w-]{10,12}) $%x';

            // Test if $value is Video entity then set the new value.
            foreach ($col as $value) {
                if ($value instanceof Video) {
                    // dump($value->getName());
                    preg_match($pattern, $value->getName(), $matches);

                    $id = $matches[1] ?? ''; // condition necessary bug undefinied array Key
                    $newUrl = 'https://www.youtube.com/embed/'.$id;
                    $value->setName($newUrl);

                    // Changing primitive fields or associations requires you to explicitly trigger a re-computation of the changeset of the affected entity.
                    $uow->recomputeSingleEntityChangeSet($col->getTypeClass(), $value);
                }
            }
        }
    }
    

    /**
     * setDateTimeComment
     * Set createdAt to Comment.
     */
    private function setDateTimeComment(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Comment) {
            return;
        }

        $entity->setCreatedAt(new \DateTime());        
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
        $entity->setSlug((string) $slugName);
    }
}
