import {
  HomeActionCards,
  HomeArticles,
  HomeCta,
  HomeExplore,
  HomeHero,
  HomeServices,
  HomeSpecialties,
  HomeStats,
  HomeTeam,
  HomeTrustBar,
} from '../components/home/FigmaHome';

export default function HomePage() {
  return (
    <main id="contenido">
      <HomeHero />
      <HomeTrustBar />
      <HomeStats />
      <HomeActionCards />
      <HomeExplore />
      <HomeSpecialties />
      <HomeServices />
      <HomeTeam />
      <HomeArticles />
      <HomeCta />
    </main>
  );
}
