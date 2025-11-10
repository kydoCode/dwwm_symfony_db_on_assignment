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
        // Création des auteurs
        $auteurs = [
            'Marie Dubois' => 'Journaliste tech passionnée',
            'Jean Martin' => 'Développeur full-stack',
            'Sophie Laurent' => 'Designer UX/UI',
            'Pierre Durand' => 'Chef de projet digital'
        ];

        $auteurEntities = [];
        foreach ($auteurs as $nom => $description) {
            $auteur = new Auteur();
            $auteur->setNom($nom);
            $manager->persist($auteur);
            $auteurEntities[] = $auteur;
        }

        // Articles de démonstration
        $articles = [
            [
                'title' => 'Introduction au Design Glassmorphique',
                'content' => "Le glassmorphisme est une tendance design qui simule l'effet du verre dépoli. Cette approche crée des interfaces modernes et élégantes.\n\nCaractéristiques principales :\n- Transparence et flou d'arrière-plan\n- Bordures subtiles\n- Ombres douces\n- Hiérarchie visuelle claire\n\nCette technique améliore l'expérience utilisateur tout en conservant une esthétique moderne.",
                'auteur' => 0
            ],
            [
                'title' => 'Symfony 6 : Les Nouveautés',
                'content' => "Symfony 6 apporte de nombreuses améliorations pour les développeurs.\n\nPrincipales nouveautés :\n- PHP 8.1 minimum requis\n- AssetMapper pour la gestion des assets\n- Améliorations des performances\n- Nouvelle syntaxe pour les attributs\n\nCes évolutions rendent le développement plus efficace et moderne.",
                'auteur' => 1
            ],
            [
                'title' => 'UX Design : Principes Fondamentaux',
                'content' => "L'expérience utilisateur (UX) est cruciale pour le succès d'une application.\n\nPrincipes clés :\n- Simplicité et clarté\n- Consistance dans l'interface\n- Feedback utilisateur\n- Accessibilité pour tous\n- Tests utilisateurs réguliers\n\nUn bon UX design augmente la satisfaction et l'engagement des utilisateurs.",
                'auteur' => 2
            ],
            [
                'title' => 'Gestion de Projet Agile',
                'content' => "La méthodologie Agile transforme la gestion de projet informatique.\n\nAvantages de l'Agile :\n- Flexibilité et adaptabilité\n- Livraisons fréquentes\n- Collaboration étroite\n- Amélioration continue\n- Réduction des risques\n\nCette approche favorise l'innovation et la réactivité aux changements.",
                'auteur' => 3
            ],
            [
                'title' => 'Bootstrap 5 : Guide Complet',
                'content' => "Bootstrap 5 simplifie le développement d'interfaces responsives.\n\nNouvelles fonctionnalités :\n- Suppression de jQuery\n- Nouvelles classes utilitaires\n- Système de grille amélioré\n- Composants modernisés\n- Meilleure personnalisation\n\nUn framework incontournable pour le développement web moderne.",
                'auteur' => 1
            ],
            [
                'title' => 'Accessibilité Web : Bonnes Pratiques',
                'content' => "L'accessibilité web garantit l'usage pour tous les utilisateurs.\n\nRègles WCAG essentielles :\n- Contrastes suffisants\n- Navigation au clavier\n- Textes alternatifs\n- Structure sémantique\n- Tailles de police adaptées\n\nUne approche inclusive bénéficie à tous les utilisateurs.",
                'auteur' => 2
            ]
        ];

        foreach ($articles as $index => $articleData) {
            $article = new Article();
            $article->setTitle($articleData['title']);
            $article->setContent($articleData['content']);
            $article->setPublishedAt(new \DateTimeImmutable('-' . ($index + 1) . ' days'));
            $article->setAuteur($auteurEntities[$articleData['auteur']]);
            
            $manager->persist($article);
        }

        $manager->flush();
    }
}
