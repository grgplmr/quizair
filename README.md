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


## Installation

1. Upload the plugin folder to `wp-content/plugins` or install it through the WordPress plugin uploader.
2. Install and activate the **Advanced Custom Fields** plugin. The quiz questions rely on the `choices`, `answer` and `explanation` fields created with ACF.
3. Activate **ACME BIAQuiz** from the Plugins menu. Default categories will be created automatically.

## Usage

Insert the shortcode `[acme_bia_quiz]` into any post or page. Use the optional `category` attribute to display questions from a specific category slug:

```
[acme_bia_quiz category="meteo"]
```

## Import/Export

An Import/Export submenu is available under **BIA Questions**. Questions can be imported or exported as CSV files. The columns are:

```
category,question,option1,option2,option3,option4,correct_answer,explanation
```

`correct_answer` is the 1‑based index of the correct choice.

## REST API

Questions are accessible through `wp-json/acme-biaquiz/v1/questions`. Passing a `category` parameter filters by category. The endpoint returns up to 20 random questions.

## License

This plugin is released under the [GNU General Public License v2.0](LICENSE).

