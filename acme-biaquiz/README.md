# ACME BIAQuiz

Minimal WordPress plugin providing themed quizzes for BIA training.

Features include:
- Custom post type for questions with ACF fields `choices` and `answer`.
- Taxonomy for categories.
- Shortcode `[acme_bia_quiz category="slug"]` rendering a quiz.
- REST API endpoint returning 20 random questions per category.
- Simple JS logic repeating wrong answers until success and displaying final score.

This is a basic implementation meant as starting point for further development.
