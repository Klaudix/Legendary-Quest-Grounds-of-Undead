<?php


namespace App\Controller;


use App\Entity\Actions;
use App\Entity\Board;
use App\Entity\Mobs;
use App\Entity\User;
use App\Repository\BoardRepository;
use App\Repository\HeroClassRepository;
use App\Repository\UserRepository;
use App\Service\GameService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/game")
 */
class GameController extends AppController
{
    /**
     * @var GameServices
     */
    private $gameService;

    /**
     * @var BoardRepository
     */
    private $boardRepository;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var HeroClassRepository
     */
    private $heroClassRepository;

    public function __construct(HeroClassRepository $heroClassRepository, UserRepository $userRepository, EntityManagerInterface $em, GameService $gameService, BoardRepository $boardRepository)
    {
        $this->heroClassRepository = $heroClassRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->boardRepository = $boardRepository;
        $this->gameService = $gameService;
    }

    /**
     * @Route("/test", name="game_test")
     */
    public function indexAction(Request $request){
        [$json, $tab] = $this->gameService->boardGenerate();
        return new Response($this->Serialize($json, "json"));
        //return new Response("<style>*{font-family: monospace;}</style>".$this->gameService->displayBoard($tab));
    }

    /**
     * @Route("/map", name="game_map")
     */
    public function mapAction(){

        if(count($this->boardRepository->findAll()) == 0){
            [$json, $tab] = $this->gameService->boardGenerate();
            $map = new Board();
            $map->setJson($this->Serialize($json, 'json'));
            $map->setName(time());

            $this->em->persist($map);
            $this->em->flush();
        }
        else{
            $json = $this->boardRepository->last()->getJson();
            return new Response($json);
        }


        return new Response($this->Serialize($json , "json"));
    }

    /**
     * @Route("/hero")
     */
    public function heroAction()
    {
        $users = $this->userRepository->findCharacters();
        return new Response($this->Serialize($users, "json"));
    }

    /**
     * @Route("/move/{id}/{X}/{Y}", requirements={"id":"\d+", "X":"\d+", "Y":"\d+"})
     * @param User $id
     * @param int $X
     * @param int $Y
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function moveAction(User $id, int $X, int $Y ){
        $actions = new Actions($id);
        $actions->setPosition("{$X};{$Y}");

        $this->em->persist($actions);
        $this->em->flush();
        return new JsonResponse([["id"=>$id->getId()], ["X"=>$X], ["Y"=>$Y]]);
    }

    /**
     * @Route("/user")
     */
    public function userAction(){
        $hero = $this->userRepository->find(1);
        $master = $this->userRepository->find(2);

        if(!$hero->isActive()){
            $array = [
              "id" => 1
            ];
            $hero->setIsActive(true);
        }
        else if (!$master->isActive()){
            $array = [
                "id"=> 2
            ];
            $master->setIsActive(true);
        }
        else{
            $array = [
                "id"=>0
            ];
        }

        $this->em->persist($hero);
        $this->em->persist($master);

        $this->em->flush();
        return new JsonResponse($array);
    }

    /**
     * @Route("/clear")
     */
    public function clearAction(){
        $hero = $this->userRepository->find(1);
        $master = $this->userRepository->find(2);

        $hero->setIsActive(0);
        $master->setIsActive(0);

        $this->em->persist($hero);
        $this->em->persist($master);
        $this->em->flush();

        return new Response("true");
    }

}