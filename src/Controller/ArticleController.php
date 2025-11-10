<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Auteur;
use App\Repository\ArticleRepository;
use App\Repository\AuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    private ArticleRepository $articleRepository;
    private AuteurRepository $auteurRepository;
    private EntityManagerInterface $em;

    public function __construct(ArticleRepository $articleRepository, AuteurRepository $auteurRepository, EntityManagerInterface $em)
    {
        $this->articleRepository = $articleRepository;
        $this->auteurRepository = $auteurRepository;
        $this->em = $em;
    }

    #[Route('/list', name: 'article_index', methods: ['GET'])]
    public function index(): Response
    {
        // TODO: Récupérer tous les articles depuis le repository
        // Indice : quelle méthode du repository retourne tous les enregistrements ?
        
        return $this->render('article/list.html.twig', ['articles' => []]);
    }

    #[Route('/new', name: 'article_new', methods: ['POST','GET'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // TODO: Récupérer les données du formulaire (title, content, author)
            // Indice : $request->request->get('nom_du_champ')
            
            // TODO: Créer les instances des entités Auteur et Article
            // Indice : new Auteur(), new Article(), puis utiliser les setters
            
            // TODO: Définir la date de publication
            // Indice : new \DateTimeImmutable()
            
            // TODO: Associer l'auteur à l'article
            // Indice : quelle méthode permet de définir l'auteur d'un article ?
            
            // TODO: Persister les entités et sauvegarder
            // Indice : persist() puis flush()
            
            // TODO: Rediriger vers la liste des articles
            // Indice : redirectToRoute() avec le nom de la route
        }

        return $this->render('article/create_modal.html.twig');
    }

    #[Route('/{id}', name: 'article_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        // TODO: Récupérer l'article par son ID
        // Indice : quelle méthode du repository prend un ID en paramètre ?
        
        // TODO: Vérifier que l'article existe, sinon retourner une erreur 404
        // Indice : if (!$article) { return new Response(..., 404); }
        
        // TODO: Afficher le template detail_modal.html.twig
        // Indice : render() avec le nom du template et les variables
        
        return new Response('<div class="alert alert-warning">TODO: Implémenter la méthode show</div>');
    }

    #[Route('/{id}/edit', name: 'article_edit', methods: ['GET','POST'])]
    public function edit(int $id, Request $request): Response
    {
        // TODO: Récupérer l'article par son ID et vérifier qu'il existe
        // Indice : même logique que dans show()
        
        if ($request->isMethod('POST')) {
            // TODO: Mettre à jour le titre et le contenu de l'article
            // Indice : utiliser les setters de l'entité Article
            
            // TODO: Sauvegarder les modifications
            // Indice : pas besoin de persist() pour une entité déjà en base, juste flush()
            
            // TODO: Rediriger vers la liste
        }

        // TODO: Afficher le formulaire d'édition avec l'article
        // Indice : render() avec edit_modal.html.twig
        
        return new Response('<div class="alert alert-warning">TODO: Implémenter la méthode edit</div>');
    }

    #[Route('/{id}/delete', name: 'article_delete', methods: ['GET','POST'])]
    public function delete(int $id, Request $request): Response
    {
        // TODO: Récupérer l'article par son ID et vérifier qu'il existe
        // Indice : même logique que dans show() et edit()

        if ($request->isMethod('POST')) {
            // TODO: Supprimer l'article de la base de données
            // Indice : quelle méthode de l'EntityManager supprime une entité ?
            
            // TODO: Sauvegarder la suppression et rediriger
        }

        // TODO: Afficher la page de confirmation de suppression
        // Indice : render() avec delete_confirm.html.twig
        
        return new Response('<div class="alert alert-warning">TODO: Implémenter la méthode delete</div>');
    }
}
