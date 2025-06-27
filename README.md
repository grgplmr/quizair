# ACME BIAQuiz

Minimal WordPress plugin providing themed quizzes for BIA training.

Features include:
- Custom post type for questions with ACF fields `choices` and `answer`.
- Requires the Advanced Custom Fields plugin. If ACF is not active, REST and import/export features are disabled.
- Taxonomy for categories.
- Six default categories are created on activation:
  1. Aérodynamique et mécanique du vol
  2. Connaissance des aéronefs
  3. Météorologie
  4. Navigation, règlementation et sécurité des vols
  5. Histoire de l'aéronautique et de l'espace
  6. Anglais aéronautique
- Shortcode `[acme_bia_quiz category="slug"]` rendering a quiz.
- REST API endpoint returning 20 random questions per category.
- Simple JS logic repeating wrong answers until success and displaying final score.

This is a basic implementation meant as starting point for further development.
