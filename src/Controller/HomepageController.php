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

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    /**
     * index.
     */
    public function index(TrickRepository $repo): Response
    {
        $tricks = $repo->findAll();

        return $this->render('homepage/homepage.html.twig', [
            'controller_name' => 'HomepageController',
            'tricks' => $tricks,
        ]);
    }
}
