import HeroCarousel from '../components/HeroCarousel';
import CitaComprobanteBanner from '../components/cita/CitaComprobanteBanner';
import HomeSectionDivider from '../components/home/HomeSectionDivider';
import {
  HomeActionCards,
  HomeArticles,
  HomeCta,
  HomeSpecialties,
  HomeStats,
  HomeTrustBar,
} from '../components/home/FigmaHome';
import ScrollReveal from '../components/home/ScrollReveal';

export default function HomePage() {
  return (
    <main id="contenido">
      <CitaComprobanteBanner />
      <HeroCarousel />
      <ScrollReveal>
        <HomeTrustBar />
      </ScrollReveal>
      <HomeSectionDivider variant="to-image" />
      <ScrollReveal>
        <HomeStats />
      </ScrollReveal>
      <HomeSectionDivider variant="between" />
      <ScrollReveal>
        <HomeActionCards />
      </ScrollReveal>
      <HomeSectionDivider variant="to-image" />
      <ScrollReveal>
        <HomeSpecialties />
      </ScrollReveal>
      <HomeSectionDivider variant="between" />
      <ScrollReveal>
        <HomeArticles />
      </ScrollReveal>
      <HomeSectionDivider variant="to-primary" />
      <ScrollReveal>
        <HomeCta />
      </ScrollReveal>
    </main>
  );
}
