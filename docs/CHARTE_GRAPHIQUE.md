# Charte graphique — Vite & Gourmand

Cette charte décrit l'apparence utilisée dans le site. Elle complète les maquettes Figma ; les valeurs ci-dessous viennent du fichier `assets/style.css`.

## Intention

L'interface reprend une ambiance de traiteur : une couleur bordeaux pour les actions importantes, des fonds crème pour réchauffer les pages et un texte foncé pour rester lisible. Les mêmes couleurs et composants reviennent sur l'accueil, le catalogue et les espaces personnels.

## Couleurs

| Usage | Couleur | Code |
| --- | --- | --- |
| Boutons principaux, accents et liens mis en avant | Bordeaux | `#7c2938` |
| Bouton principal au survol | Bordeaux foncé | `#5e1926` |
| Fonds de sections et encarts | Crème | `#fbf6ee` |
| Fond général | Blanc cassé | `#fffdfa` |
| Texte principal | Presque noir | `#23211f` |
| Pied de page | Brun très foncé | `#292522` |
| Bordures des cartes et formulaires | Beige clair | `#e9e2d8` |

## Typographie

- Titres : **Georgia**, une police avec empattements qui rappelle une carte de restaurant.
- Texte courant : la police système utilisée par **Bootstrap**. Elle reste facile à lire sur ordinateur et téléphone.
- Petit texte au-dessus des titres : majuscules, espacement plus large et couleur bordeaux.

## Composants récurrents

- **Navigation** : logo textuel « Vite & Gourmand », liens vers les menus et le contact, bouton de connexion ou accès au compte.
- **Boutons principaux** : fond bordeaux et texte clair ; ils servent aux actions comme commander ou confirmer.
- **Cartes de menu** : image, description, prix et accès aux détails.
- **Formulaires** : libellé visible pour chaque champ, bordure légère, message d'erreur lisible.
- **Pied de page** : fond foncé, horaires et liens d'information.

## Adaptation aux écrans

La grille Bootstrap réorganise les colonnes sur les petits écrans. Les formulaires et cartes passent sous les autres éléments quand la largeur diminue. Cette adaptation doit encore être vérifiée manuellement sur plusieurs tailles d'écran.

## Accessibilité à vérifier

Le site contient un lien « Aller au contenu principal » pour la navigation clavier et des libellés pour les formulaires. Il reste à vérifier les contrastes, l'ordre de tabulation et la lisibilité des textes sur mobile. La charte ne vaut pas à elle seule validation d'accessibilité.
