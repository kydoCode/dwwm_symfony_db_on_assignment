<?php
namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Exemple de méthode personnalisée dans un Repository
     * 
     * Cette méthode récupère les articles les plus récents.
     * C'est un exemple de requête DQL (Doctrine Query Language).
     * 
     * @param int $limit Nombre maximum d'articles à récupérer (par défaut 10)
     * @return Article[] Tableau d'objets Article triés par date décroissante
     * 
     * Équivalent SQL :
     * SELECT * FROM article ORDER BY published_at DESC LIMIT 10
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')  // 'a' = alias pour Article
                    ->orderBy('a.publishedAt', 'DESC')  // Tri par date décroissante
                    ->setMaxResults($limit)  // Limite le nombre de résultats
                    ->getQuery()  // Génère la requête DQL
                    ->getResult();  // Exécute et retourne un tableau d'objets
    }

    // TODO: Créez vos propres méthodes de recherche personnalisées
    // Indice : utilisez createQueryBuilder() comme dans findRecent()
    // Voir README.md section V (DQL) pour des exemples
}
