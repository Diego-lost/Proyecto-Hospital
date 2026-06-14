import { FormEvent, useMemo, useState } from 'react';
import { Loader2, MessageCircle, Send, X } from 'lucide-react';
import { consultarAsistenteMedico, type TriageDolorResult } from '../lib/remoteCatalog';
import {
  buildClinicInfoResponse,
  shouldAnswerWithClinicInfo,
  type ClinicInfoBlock,
} from '../config/clinicInfo';
import ClinicInfoCard, { AssistantQuickActions } from './ClinicInfoCard';
import TriageReportCard from './TriageReportCard';

type ChatMsg =
  | { role: 'bot' | 'user'; kind: 'text'; text: string }
  | { role: 'bot'; kind: 'clinic'; info: ClinicInfoBlock }
  | { role: 'bot'; kind: 'medical'; result: TriageDolorResult };

const GREETING =
  'Hola, soy el asistente de NovaSalud. ¿En qué te puedo ayudar? Puedes contarme un dolor o molestia, o preguntarme por citas, horarios y contacto.';

export default function AiChatWidget() {
  const [open, setOpen] = useState(false);
  const [busy, setBusy] = useState(false);
  const [input, setInput] = useState('');
  const [messages, setMessages] = useState<ChatMsg[]>([
    { role: 'bot', kind: 'text', text: GREETING },
  ]);

  const canSend = useMemo(() => input.trim().length >= 2 && !busy, [input, busy]);

  function resetChat() {
    setInput('');
    setMessages([{ role: 'bot', kind: 'text', text: GREETING }]);
  }

  async function handleUserMessage(text: string) {
    setMessages((prev) => [...prev, { role: 'user', kind: 'text', text }]);

    if (shouldAnswerWithClinicInfo(text)) {
      const info = buildClinicInfoResponse(text);
      setMessages((prev) => [...prev, { role: 'bot', kind: 'clinic', info }]);
      return;
    }

    setBusy(true);
    try {
      const result = await consultarAsistenteMedico(text);
      setMessages((prev) => [...prev, { role: 'bot', kind: 'medical', result }]);
    } catch (e) {
      const msg = e instanceof Error ? e.message : 'No pude analizar tu consulta en este momento.';
      setMessages((prev) => [...prev, { role: 'bot', kind: 'text', text: msg }]);
    } finally {
      setBusy(false);
    }
  }

  async function onSend(e: FormEvent) {
    e.preventDefault();
    if (!canSend) {
      return;
    }
    const value = input.trim();
    setInput('');
    await handleUserMessage(value);
  }

  return (
    <div className="ai-chat-widget" aria-live="polite">
      {open ? (
        <section className="ai-chat-panel" aria-label="Asistente médico virtual">
          <header className="ai-chat-header">
            <div>
              <p className="ai-chat-title">Asistente NovaSalud</p>
              <p className="ai-chat-subtitle">Orientación médica y datos de la clínica</p>
            </div>
            <button
              type="button"
              className="ai-chat-close"
              onClick={() => setOpen(false)}
              aria-label="Cerrar asistente"
            >
              <X size={16} />
            </button>
          </header>

          <AssistantQuickActions />

          <div className="ai-chat-messages">
            {messages.map((m, idx) => {
              if (m.kind === 'text') {
                return (
                  <p
                    key={`${m.role}-${idx}`}
                    className={`ai-chat-bubble ${m.role === 'bot' ? 'ai-chat-bubble--bot' : 'ai-chat-bubble--user'}`}
                  >
                    {m.text}
                  </p>
                );
              }
              if (m.kind === 'clinic') {
                return (
                  <div key={`clinic-${idx}`} className="ai-chat-bubble ai-chat-bubble--bot ai-chat-bubble--card">
                    <ClinicInfoCard info={m.info} />
                  </div>
                );
              }
              return (
                <div key={`medical-${idx}`} className="ai-chat-bubble ai-chat-bubble--bot ai-chat-bubble--card">
                  <TriageReportCard result={m.result} />
                </div>
              );
            })}
            {busy ? (
              <p className="ai-chat-bubble ai-chat-bubble--bot">
                <Loader2 size={14} className="inline animate-spin" /> Analizando tu consulta...
              </p>
            ) : null}
          </div>

          <form className="ai-chat-form" onSubmit={(e) => void onSend(e)}>
            <input
              className="ai-chat-input"
              value={input}
              onChange={(e) => setInput(e.target.value)}
              placeholder="Ej: me duele el diente, quiero agendar cita..."
              disabled={busy}
            />
            <button className="ai-chat-send" type="submit" disabled={!canSend}>
              <Send size={16} />
            </button>
          </form>
          <button type="button" className="ai-chat-reset" onClick={resetChat}>
            Nueva consulta
          </button>
        </section>
      ) : (
        <button type="button" className="ai-chat-launcher" onClick={() => setOpen(true)}>
          <MessageCircle size={18} />
          Asistente IA
        </button>
      )}
    </div>
  );
}
