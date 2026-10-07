"""Export the approved dark B identity in repository and platform formats."""
from pathlib import Path
from PIL import Image,ImageDraw,ImageFont
import shutil,json
root=Path(__file__).resolve().parents[1];source=root/'assets-github/variants/B';out=root/'assets-github/approved';out.mkdir(parents=True,exist_ok=True)
regular='/System/Library/Fonts/Supplemental/Arial.ttf';bold='/System/Library/Fonts/Supplemental/Arial Bold.ttf';bg='#14243e';fg='#ffffff';accent='#8bc4ff';muted='#c1d2e8'
for lang in ['en','de']:
 p=source/f'banner-{lang}.svg';s=p.read_text().replace('Oxygen QuickNav B '+lang,'Oxygen QuickNav '+lang).replace(('Artwork proposal ' if lang=='en' else 'Grafikentwurf ')+'B','QuickNav');(out/f'banner-{lang}.svg').write_text(s)
 img=Image.open(source/f'banner-{lang}.png');d=ImageDraw.Draw(img);d.rectangle((60,560,700,615),fill=bg);d.text((66,570),'deckerweb  ·  QuickNav',font=ImageFont.truetype(regular,18),fill=muted);img.save(out/f'banner-{lang}.png')
for name in ['icon.svg','icon-128.png','icon-256.png']:shutil.copy2(source/name,out/name)
# A separately composed wide banner avoids cropping the approved portrait-like layout.
for lang in ['en','de']:
 for w,h in [(772,250),(1544,500)]:
  scale=w/772;im=Image.new('RGB',(w,h),bg);d=ImageDraw.Draw(im)
  def text(x,y,s,size,color=fg,weight=False):d.text((round(x*scale),round(y*scale)),s,font=ImageFont.truetype(bold if weight else regular,round(size*scale)),fill=color)
  text(36,31,'Oxygen QuickNav',43,weight=True);text(38,99,'One menu. Your Oxygen.' if lang=='en' else 'Ein Menü. Dein Oxygen.',22,weight=True);text(38,145,'Oxygen 6+  /  Oxygen Classic',18,accent);text(38,208,'deckerweb  ·  QuickNav',13,muted)
  icon=Image.open(out/'icon-256.png').resize((round(186*scale),round(186*scale)),Image.Resampling.LANCZOS);im.paste(icon,(round(560*scale),round(32*scale)));im.save(out/f'wp-banner-{lang}-{w}x{h}.png')
 icon=(out/'icon.svg').read_text();inside=icon[icon.index('>')+1:icon.rindex('</svg>')]
 slogan='One menu. Your Oxygen.' if lang=='en' else 'Ein Menü. Dein Oxygen.'
 (out/f'wp-banner-{lang}.svg').write_text(f'<svg xmlns="http://www.w3.org/2000/svg" width="1544" height="500" viewBox="0 0 772 250"><title>Oxygen QuickNav</title><rect width="772" height="250" fill="{bg}"/><g font-family="Arial,sans-serif" fill="{fg}"><text x="36" y="70" font-size="43" font-weight="700">Oxygen QuickNav</text><text x="38" y="120" font-size="22" font-weight="700">{slogan}</text><text x="38" y="164" font-size="18" fill="{accent}">Oxygen 6+ / Oxygen Classic</text><text x="38" y="221" font-size="13" fill="{muted}">deckerweb · QuickNav</text></g><svg x="560" y="32" width="186" height="186" viewBox="0 0 256 256">{inside}</svg></svg>')
brand=root/'assets/brand';brand.mkdir(exist_ok=True)
for name in ['icon.svg','icon-128.png','icon-256.png']:shutil.copy2(out/name,brand/name)
(root/'assets-github/design.json').write_text(json.dumps({'approved_variant':'B','approved_on':'2026-10-08','copyright':'2026 David Decker – DECKERWEB','license':'GPL-2.0-or-later','source':'Original vector artwork; drafts retained under variants','fonts':'System Arial references only; no font binaries distributed'},indent=2)+'\n')
print('Approved B: bilingual GitHub and WordPress banners, SVG masters, 128/256 PNG icons.')
