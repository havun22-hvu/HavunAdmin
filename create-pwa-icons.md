# PWA Iconen Maken voor Havun Admin

## Optie 1: Online Generator (Snelst)

1. Ga naar: https://www.pwabuilder.com/imageGenerator
2. Upload je favicon.png of maak een nieuw icoon met "HA" logo
3. Download de gegenereerde iconen
4. Plaats icon-192x192.png en icon-512x512.png in /public/

## Optie 2: Gebruik Figma/Canva

1. Maak een 512x512 canvas
2. Achtergrond: Gradient van #6366F1 naar #4F46E5 (Indigo)
3. Tekst: "HA" in wit, bold, gecentreerd
4. Export als PNG in twee formaten:
   - icon-192x192.png (192x192)
   - icon-512x512.png (512x512)
5. Plaats in /public/

## Optie 3: Gebruik generate-icons.html

1. Open in browser: http://localhost:8000/generate-icons.html
2. Klik met rechtermuisknop op elk icoon
3. "Opslaan als..." → Sla op als:
   - icon-192x192.png
   - icon-512x512.png
4. Plaats beide bestanden in /public/

## Optie 4: Gebruik je huidige favicon.png

Als je al een favicon.png hebt die geschikt is:

```bash
# Kopieer en hernoem (tijdelijk)
cp public/favicon.png public/icon-192x192.png
cp public/favicon.png public/icon-512x512.png
```

Later kun je professionele iconen laten maken.

## Verificatie

Na het plaatsen van de iconen, test of de PWA werkt:

1. Open staging.admin.havun.nl op je smartphone
2. Klik op browser menu (Chrome: 3 dots)
3. Klik op "Toevoegen aan startscherm" of "Installeren"
4. De app verschijnt als icoon op je homescreen!
