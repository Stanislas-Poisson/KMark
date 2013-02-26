![KMark Logo](http://www.stanislas-poisson.fr/img/KMark.png "KMark")

# KMark
Langage: PHP 5 poo 0.5(beta) - 26/02/2013  
Par [Stanislas Poisson](http://www.stanislas-poisson.fr/ "Stanislas Poisson")

## Introduction
KMark est une adaptation de la célèbre forme d'écriture MarkDown, qui se base sur une typographie correspondant aux e-mails de type text afin de générer un contenu en html. Etant donner que le MarkDown n'est pas conçu pour réaliser des composition complete et complexe en html, une amélioration divergante était (de mon point de vue) nécéssaire.

### Exigence d'installation
Cette librairie nécessite PHP 5.3 ou supérieur.

Il suffit ensuite de l'inclure via _include(_once)_ ou _require(_once)_ en début de projet.

### Syntaxe
#### Titre Hn
Les éléments de type H1 à H6 sont généré via un ou plusieurs dièses (le nombre déterminant le niveaux du titre) accompagné d'un espace.

	# Mon titre
	<h1>Mon titre</h1>
	
	## Mon sous-titre
	<h2>Mon sous-titre</h2>

#### Liste non-ordonné/ordonné
##### Liste non-ordonné
Utiliser un plus accompagné d'une tabulation pour mettre en place une liste non-ordonné, pour mettre des sous liste, décaler simplement d'une tabulation pour chaque niveau.

	+	Elem 1
	+	Elem 2
		+	Elem 2.1
		+	Elem 2.2
	+	Elem 3
	<ul>
		<li>Elem 1</li>
		<li>Elem 2<ul>
			<li>Elem 2.1</li>
			<li>Elem 2.1</li>
		</ul></li>
		<li>Elem 3</li>
	</ul>

#### Stylisation:
##### Gras :
Utiliser un astérisque accompagné d'un espace autour du mot ou de l'ensemble de mots à mettre en gras. L'ensemble sera mis dans un span ayant pour class **_b_**.

	* foo *
	<span class="b">foo</span>

##### Italique :
Utiliser un tiret accompagné d'un espace autour du mot ou de l'ensemble de mots à mettre en italique. L'ensemble sera mis dans un span ayant pour class **_i_**.

	- foo -
	<span class="i">foo</span>

##### Souligné :
Utiliser une underscore accompagné d'un espace autour du mot ou de l'ensemble de mots à mettre en souligné. L'ensemble sera mis dans un span ayant pour class **_u_**.

	_ foo _
	<span class="u">foo</span>

##### Barré :
Utiliser une slash accompagné d'un espace autour du mot ou de l'ensemble de mots à mettre en barré. L'ensemble sera mis dans un span ayant pour class **_d_**.

	/ foo /
	<span class="d">foo</span>

Il est bien entendu possible de cumulés les styles ci-dessus, le span ayant alors les classe css demandées.

	_-* foo *-_
	<span class="u i b">foo</span>



### Copyright et Licence

Copyright © 2013 - Stanislas Poisson  
[www.stanislas-poisson.fr](http://www.stanislas-poisson.fr "Stanislas Poisson")  
Tous droits réservés.

Ce logiciel est fourni par M. Poisson Stanislas "tel quel" et aucune garantie expresse ou implicite, y compris, mais sans s'y limiter, les garanties implicites de qualité marchande et d'adéquation à un usage particulier sont rejetées.  
En aucun cas, le propriétaire du copyright ou contributeurs peut être tenu responsable des dommages directs, indirects, fortuits, spéciaux, exemplaires ou consécutifs (y compris, mais sans s'y limiter, l'achat de biens ou services de substitution, la perte d'utilisation, de données ou de profits; ou interruption d'activité) résultant et sur toute théorie de responsabilité, qu'elle soit contractuelle, de responsabilité stricte ou délictuelle (y compris la négligence ou autre) découlant de quelque façon de l'utilisation de ce logiciel, même si elle est notifiée de l'éventualité de tels dommages.
