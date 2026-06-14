import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { publicAsset } from '../lib/publicAsset';

const ROTATE_MS = 6000;

type HeroSlide = {
  image: string;
  alt: string;
  title: string;
  meta: string;
  tagline: string;
  ctaLabel: string;
  ctaTo: string;
};

const slides: HeroSlide[] = [
  {
    image: publicAsset('img/hero/slide-consulta.jpg'),
    alt: 'Profesional de salud en consulta médica',
    title: 'Clínica NovaSalud',
    meta: 'Atención ambulatoria y hospitalaria · Lima',
    tagline: 'Salud de primer nivel con un enfoque humano y tecnología clínica.',
    ctaLabel: 'Agendar cita',
    ctaTo: '/cita',
  },
  {
    image: publicAsset('img/hero/slide-equipo.jpg'),
    alt: 'Equipo médico en trabajo colaborativo',
    title: 'Especialistas integrados',
    meta: 'Cardiología, pediatría, traumatología y más',
    tagline: 'Un equipo calificado te acompaña antes, durante y después de tu atención.',
    ctaLabel: 'Ver especialidades',
    ctaTo: '/especialidades',
  },
  {
    image: publicAsset('img/hero/slide-hospital.jpg'),
    alt: 'Instalaciones hospitalarias modernas',
    title: 'Tecnología y confianza',
    meta: 'Laboratorio, imágenes y emergencia 24 horas',
    tagline: 'Resultados confiables, diagnóstico oportuno y atención cuando más lo necesitas.',
    ctaLabel: 'Conocer servicios',
    ctaTo: '/#servicios',
  },
];

export default function HeroCarousel() {
  const [active, setActive] = useState(0);

  useEffect(() => {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced || slides.length < 2) return undefined;

    const id = window.setInterval(() => {
      setActive((i) => (i + 1) % slides.length);
    }, ROTATE_MS);

    return () => window.clearInterval(id);
  }, []);

  const slide = slides[active];

  return (
    <section className="hero-banner" aria-label="Presentación principal" aria-roledescription="carrusel">
      <div className="hero-banner__slides" aria-hidden="true">
        {slides.map((s, i) => (
          <div
            key={s.image}
            className={`hero-banner__slide${i === active ? ' is-active' : ''}`}
            style={{ backgroundImage: `url(${s.image})` }}
            role="img"
            aria-label={s.alt}
          />
        ))}
      </div>

      <div className="hero-banner__scrim" aria-hidden="true" />

      <div className="container hero-banner__layout">
        <div className="hero-banner__primary">
          <h1 className="hero-banner__title">{slide.title}</h1>
          <p className="hero-banner__meta">{slide.meta}</p>
        </div>

        <div className="hero-banner__secondary">
          <p className="hero-banner__tagline">{slide.tagline}</p>
          <Link className="btn btn--hero" to={slide.ctaTo}>
            {slide.ctaLabel}
          </Link>
        </div>
      </div>

      <div className="hero-banner__dots" role="tablist" aria-label="Diapositivas del banner">
        {slides.map((s, i) => (
          <button
            key={s.image}
            type="button"
            role="tab"
            className={`hero-banner__dot${i === active ? ' is-active' : ''}`}
            aria-selected={i === active}
            aria-label={`Diapositiva ${i + 1}: ${s.title}`}
            onClick={() => setActive(i)}
          />
        ))}
      </div>
    </section>
  );
}
