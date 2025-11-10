# Guide Étudiant - Symfony CRUD avec Doctrine

> Guide complet pour maîtriser Doctrine ORM et créer une application CRUD avec Symfony

---

## I. Configuration Initiale

### 1.1 Configuration de la base de données (`.env`)

Le fichier `.env` contient la configuration de votre base de données :

```env
# SQLite (recommandé pour débuter)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# MySQL
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/articles_db?serverVersion=8.0.32&charset=utf8mb4"

# PostgreSQL
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/articles_db?serverVersion=16&charset=utf8"
```

> **Astuce** : Utilisez SQLite pour commencer, c'est plus simple (pas de serveur à installer).

### 1.2 Création de la base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Vérifier la connexion
php bin/console doctrine:query:sql "SELECT 1"
```

> **Résultat attendu** : `array(1) { [0]=> array(1) { [1]=> int(1) } }`

### 1.3 Migrations

Les migrations permettent de créer/modifier la structure de la base de données.

```bash
# Générer une migration (après avoir créé/modifié des entités)
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir l'état des migrations
php bin/console doctrine:migrations:status
```

> **Important** : Toujours générer une migration après avoir modifié vos entités !

---

## II. Entités et Relations

### 2.1 Création des entités

#### Entité `Auteur`

```php
<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Auteur
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: "auteur", targetEntity: Article::class, cascade: ["persist", "remove"])]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    // Getters et setters...
}
```

#### Entité `Article`

```php
<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Article
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: Auteur::class, inversedBy: "articles")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Auteur $auteur = null;

    // Getters et setters...
}
```

> **Commande utile** : `php bin/console make:entity` pour créer/modifier une entité

### 2.2 Relations OneToMany / ManyToOne

#### Schéma de la relation

```
Auteur (1) ←→ (N) Article
   ↑                ↓
OneToMany      ManyToOne
```

- **Un auteur** peut avoir **plusieurs articles** → `OneToMany`
- **Un article** appartient à **un seul auteur** → `ManyToOne`

#### Annotations importantes

```php
// Côté Auteur (OneToMany)
#[ORM\OneToMany(
    mappedBy: "auteur",              // Propriété dans Article
    targetEntity: Article::class,     // Entité cible
    cascade: ["persist", "remove"]    // Actions en cascade
)]
private Collection $articles;

// Côté Article (ManyToOne)
#[ORM\ManyToOne(
    targetEntity: Auteur::class,      // Entité cible
    inversedBy: "articles"            // Propriété dans Auteur
)]
#[ORM\JoinColumn(nullable: false)]    // Clé étrangère obligatoire
private ?Auteur $auteur = null;
```

### 2.3 Méthodes `addArticle()` et `removeArticle()`

Ces méthodes maintiennent la cohérence de la relation bidirectionnelle.

```php
// Dans l'entité Auteur
public function addArticle(Article $article): static
{
    if (!$this->articles->contains($article)) {
        $this->articles->add($article);
        $article->setAuteur($this);  // Synchronisation
    }
    return $this;
}

public function removeArticle(Article $article): static
{
    if ($this->articles->removeElement($article)) {
        if ($article->getAuteur() === $this) {
            $article->setAuteur(null);  // Synchronisation
        }
    }
    return $this;
}
```

#### Utilisation

```php
$auteur = new Auteur();
$auteur->setNom("Marie Dubois");

$article = new Article();
$article->setTitle("Mon article");

// Méthode 1 : Via l'auteur
$auteur->addArticle($article);  // Associe automatiquement l'auteur à l'article

// Méthode 2 : Via l'article
$article->setAuteur($auteur);   // Plus direct

$em->persist($auteur);
$em->flush();
```

> **Cascade persist** : Si vous persistez l'auteur, les articles associés seront automatiquement persistés.

---

## III. Fixtures

### 3.1 Installation

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

### 3.2 Création du fichier de fixtures

**Fichier** : `src/DataFixtures/AppFixtures.php`

```php
<?php
namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Auteur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des auteurs
        $auteur1 = new Auteur();
        $auteur1->setNom("Marie Dubois");
        $manager->persist($auteur1);

        $auteur2 = new Auteur();
        $auteur2->setNom("Jean Martin");
        $manager->persist($auteur2);

        // Créer des articles
        $article1 = new Article();
        $article1->setTitle("Introduction à Symfony")
                 ->setContent("Symfony est un framework PHP...")
                 ->setPublishedAt(new \DateTimeImmutable())
                 ->setAuteur($auteur1);
        $manager->persist($article1);

        $article2 = new Article();
        $article2->setTitle("Doctrine ORM")
                 ->setContent("Doctrine est un ORM puissant...")
                 ->setPublishedAt(new \DateTimeImmutable('-1 day'))
                 ->setAuteur($auteur1);
        $manager->persist($article2);

        $manager->flush();
    }
}
```

### 3.3 Commandes utiles

```bash
# Charger les fixtures (vide la base de données)
php bin/console doctrine:fixtures:load

# Charger sans confirmation
php bin/console doctrine:fixtures:load --no-interaction

# Ajouter des fixtures sans vider (avec extension)
php bin/console doctrine:fixtures:load --append
```

> **Attention** : 
`doctrine:fixtures:load` supprime toutes les données existantes !
`doctrine:fixtures:load --append`, si répété, va générer des doublons.

---

## IV. CRUD et Repository

### 4.1 Récupération du Repository

```php
// Dans un contrôleur
use App\Repository\ArticleRepository;

class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository
    ) {}

    public function index(): Response
    {
        // Le repository est injecté automatiquement
        $articles = $this->articleRepository->findAll();
        // ...
    }
}
```

### 4.2 Méthodes de base du Repository

#### `find($id)` - Récupérer par ID

```php
$article = $articleRepository->find(1);

if (!$article) {
    throw $this->createNotFoundException('Article non trouvé');
}
```

#### `findAll()` - Récupérer tous les enregistrements

```php
$articles = $articleRepository->findAll();
// Retourne un tableau d'objets Article
```

#### `findBy()` - Récupérer avec critères

```php
// Syntaxe : findBy(critères, ordre, limite, offset)

// Tous les articles d'un auteur
$articles = $articleRepository->findBy(
    ['auteur' => $auteur],
    ['publishedAt' => 'DESC']
);

// Les 5 derniers articles
$articles = $articleRepository->findBy(
    [],
    ['publishedAt' => 'DESC'],
    5  // limite
);

// Articles paginés
$articles = $articleRepository->findBy(
    [],
    ['publishedAt' => 'DESC'],
    10,  // limite
    20   // offset (page 3)
);
```

#### `findOneBy()` - Récupérer un seul enregistrement

```php
// Premier article avec ce titre
$article = $articleRepository->findOneBy(['title' => 'Mon titre']);

// Dernier article publié
$article = $articleRepository->findOneBy(
    [],
    ['publishedAt' => 'DESC']
);
```

### 4.3 CRUD - Création (Create)

```php
public function create(Request $request, EntityManagerInterface $em): Response
{
    if ($request->isMethod('POST')) {
        // 1. Créer les entités
        $auteur = new Auteur();
        $auteur->setNom($request->request->get('author'));

        $article = new Article();
        $article->setTitle($request->request->get('title'))
                ->setContent($request->request->get('content'))
                ->setPublishedAt(new \DateTimeImmutable())
                ->setAuteur($auteur);

        // 2. Persister (préparer l'insertion)
        $em->persist($auteur);
        $em->persist($article);

        // 3. Flush (exécuter les requêtes SQL)
        $em->flush();

        return $this->redirectToRoute('article_index');
    }
    // ...
}
```

> **Note** : `persist()` ne fait qu'enregistrer l'objet dans l'UnitOfWork. C'est `flush()` qui exécute les requêtes SQL.

### 4.4 CRUD - Lecture (Read)

```php
public function show(int $id, ArticleRepository $repo): Response
{
    // Récupérer l'article
    $article = $repo->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }

    return $this->render('article/show.html.twig', [
        'article' => $article
    ]);
}
```

### 4.5 CRUD - Mise à jour (Update)

```php
public function edit(int $id, Request $request, ArticleRepository $repo, EntityManagerInterface $em): Response
{
    $article = $repo->find($id);

    if (!$article) {
        throw $this->createNotFoundException();
    }

    if ($request->isMethod('POST')) {
        // Modifier l'entité
        $article->setTitle($request->request->get('title'))
                ->setContent($request->request->get('content'));

        // Pas besoin de persist() pour une entité déjà gérée
        $em->flush();

        return $this->redirectToRoute('article_index');
    }
    // ...
}
```

> **Important** : Pas besoin de `persist()` pour une entité déjà récupérée depuis la base !

### 4.6 CRUD - Suppression (Delete)

```php
public function delete(int $id, ArticleRepository $repo, EntityManagerInterface $em): Response
{
    $article = $repo->find($id);

    if (!$article) {
        throw $this->createNotFoundException();
    }

    // Supprimer l'entité
    $em->remove($article);
    $em->flush();

    return $this->redirectToRoute('article_index');
}
```

### 4.7 Gestion des relations en cascade

#### Option `cascade: ["persist"]`

```php
#[ORM\OneToMany(mappedBy: "auteur", targetEntity: Article::class, cascade: ["persist"])]
private Collection $articles;
```

**Effet** : Quand vous persistez l'auteur, les articles associés sont automatiquement persistés.

```php
$auteur = new Auteur();
$auteur->setNom("Marie");

$article = new Article();
$article->setTitle("Mon article");
$auteur->addArticle($article);

$em->persist($auteur);  // Persiste aussi l'article automatiquement
$em->flush();
```

#### Option `cascade: ["remove"]`

```php
#[ORM\OneToMany(mappedBy: "auteur", targetEntity: Article::class, cascade: ["remove"])]
private Collection $articles;
```

**Effet** : Quand vous supprimez l'auteur, tous ses articles sont supprimés.

```php
$em->remove($auteur);  // Supprime aussi tous ses articles
$em->flush();
```

> **Attention** : Utilisez `cascade: ["remove"]` avec précaution !

---

## V. DQL (Doctrine Query Language)

### 5.1 Qu'est-ce que le DQL ?

Le DQL est un langage de requête orienté objet, similaire au SQL mais qui travaille avec des **entités** et non des tables.

### 5.2 Syntaxe de base

#### Créer une requête DQL

```php
// Dans ArticleRepository.php
public function findRecent(int $limit = 10): array
{
    return $this->createQueryBuilder('a')
        ->orderBy('a.publishedAt', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}
```

### 5.3 Équivalences SQL ↔ DQL

| SQL | DQL | Exemple |
|-----|-----|---------|
| `SELECT *` | `SELECT a` | `SELECT a FROM App\Entity\Article a` |
| `FROM articles` | `FROM App\Entity\Article a` | Utilise le nom de l'entité |
| `WHERE title = ?` | `WHERE a.title = :title` | Paramètres nommés |
| `JOIN auteurs` | `JOIN a.auteur au` | Utilise les relations |
| `ORDER BY date` | `ORDER BY a.publishedAt` | Propriétés de l'entité |

### 5.4 Exemples de requêtes DQL

#### Requête simple

```php
public function findByTitle(string $title): array
{
    return $this->createQueryBuilder('a')
        ->where('a.title LIKE :title')
        ->setParameter('title', '%' . $title . '%')
        ->getQuery()
        ->getResult();
}
```

**Équivalent SQL** :
```sql
SELECT * FROM article WHERE title LIKE '%recherche%'
```

#### Requête avec JOIN

```php
public function findByAuthorName(string $name): array
{
    return $this->createQueryBuilder('a')
        ->join('a.auteur', 'au')
        ->where('au.nom = :name')
        ->setParameter('name', $name)
        ->getQuery()
        ->getResult();
}
```

**Équivalent SQL** :
```sql
SELECT a.* FROM article a
JOIN auteur au ON a.auteur_id = au.id
WHERE au.nom = 'Marie Dubois'
```

#### Requête avec COUNT

```php
public function countByAuthor(Auteur $auteur): int
{
    return $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.auteur = :auteur')
        ->setParameter('auteur', $auteur)
        ->getQuery()
        ->getSingleScalarResult();
}
```

#### Requête avec plusieurs conditions

```php
public function findRecentByAuthor(Auteur $auteur, int $days = 7): array
{
    $date = new \DateTimeImmutable("-{$days} days");

    return $this->createQueryBuilder('a')
        ->where('a.auteur = :auteur')
        ->andWhere('a.publishedAt >= :date')
        ->setParameter('auteur', $auteur)
        ->setParameter('date', $date)
        ->orderBy('a.publishedAt', 'DESC')
        ->getQuery()
        ->getResult();
}
```

### 5.5 Méthodes du QueryBuilder

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `select()` | Sélectionner des champs | `->select('a.title, a.content')` |
| `where()` | Condition WHERE | `->where('a.id = :id')` |
| `andWhere()` | Ajouter une condition AND | `->andWhere('a.title LIKE :title')` |
| `orWhere()` | Ajouter une condition OR | `->orWhere('a.content IS NULL')` |
| `join()` | Jointure INNER JOIN | `->join('a.auteur', 'au')` |
| `leftJoin()` | Jointure LEFT JOIN | `->leftJoin('a.auteur', 'au')` |
| `orderBy()` | Tri | `->orderBy('a.publishedAt', 'DESC')` |
| `groupBy()` | Groupement | `->groupBy('a.auteur')` |
| `setMaxResults()` | Limite | `->setMaxResults(10)` |
| `setFirstResult()` | Offset | `->setFirstResult(20)` |
| `setParameter()` | Paramètre | `->setParameter('id', 1)` |

### 5.6 Méthodes d'exécution

```php
// Retourner un tableau d'objets
->getQuery()->getResult();

// Retourner un seul objet (ou null)
->getQuery()->getOneOrNullResult();

// Retourner une valeur scalaire (COUNT, SUM, etc.)
->getQuery()->getSingleScalarResult();

// Retourner un tableau associatif
->getQuery()->getArrayResult();
```

---

## Ressources Complémentaires

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
- [Doctrine Query Language](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/dql-doctrine-query-language.html)

---

## Checklist de progression

- [ ] Configuration de la base de données
- [ ] Création des entités Auteur et Article
- [ ] Ajout des relations OneToMany/ManyToOne
- [ ] Génération et exécution des migrations
- [ ] Création des fixtures
- [ ] Implémentation du CRUD complet
- [ ] Création de requêtes DQL personnalisées
- [ ] Tests de l'application

---

**Bon courage dans votre apprentissage de Symfony et Doctrine !**

*Guide créé par [Kydo](https://github.com/kydoCode/) - 2025*
