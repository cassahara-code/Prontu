# Prontu! Assessoria em Gestão

Site institucional da **Prontu! Assessoria em Gestão** — assessoria em gestão para clínicas e consultórios da área da saúde, fundada por Laiza Barros, em Recife/PE.

> *Uma gestão eficiente, humana e estratégica.*

## Estrutura

```
site/
├── index.html        # Home — hero, valores, história, serviços, depoimentos
├── sobre.html        # A Prontu! — história, time, manifesto, números
├── servicos.html     # Planos, processo, FAQ
├── contato.html      # Formulário, WhatsApp, redes
├── css/
│   ├── tokens.css    # Design tokens (cores, tipografia, espaçamento)
│   └── base.css      # Componentes (botões, cards, header, footer, form)
├── js/
│   └── main.js       # Header sticky, menu mobile, carrossel, form
├── assets/           # Logos, monograma, retrato da fundadora
├── robots.txt
└── sitemap.xml
```

## Stack

- HTML, CSS e JavaScript vanilla — sem build, sem dependências locais
- Tipografia: Playfair Display + Inter (Google Fonts)
- Ícones: [Lucide](https://lucide.dev) via CDN
- Analytics: Google Analytics 4 (`G-J50HV6XBGJ`)
- SEO: Open Graph, Twitter Cards, JSON-LD (Organization, Person, Service, FAQPage, Breadcrumb)

## Rodar localmente

```bash
cd site
npx http-server -p 3005 -c-1 -o
```

O navegador abre em `http://localhost:3005`.

## Contato

- E-mail: ola@prontuassessoria.com.br
- WhatsApp: [(81) 9742-6889](https://wa.me/558197426889)
- Instagram: [@prontuassessoriaemgestao](https://www.instagram.com/prontuassessoriaemgestao)
- Site: https://prontuassessoria.com.br

---

Identidade visual baseada no design system oficial Prontu! · Recife, PE · Atendemos todo o Brasil.
