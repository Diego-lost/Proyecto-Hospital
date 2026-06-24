import { useEffect, useRef, useState, type ReactNode } from 'react';

type ScrollRevealProps = {
  children: ReactNode;
  className?: string;
};

export default function ScrollReveal({ children, className = '' }: ScrollRevealProps) {
  const ref = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const el = ref.current;
    if (!el) {
      return undefined;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) {
      setVisible(true);
      return undefined;
    }

    const obs = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true);
          obs.disconnect();
        }
      },
      { threshold: 0.08, rootMargin: '0px 0px -32px 0px' },
    );

    obs.observe(el);
    return () => obs.disconnect();
  }, []);

  return (
    <div ref={ref} className={`home-reveal${visible ? ' is-visible' : ''}${className ? ` ${className}` : ''}`}>
      {children}
    </div>
  );
}
