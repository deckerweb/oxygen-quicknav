"""Derive every approved banner from the same dark B vector composition."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont, ImageChops
import shutil, json, math, re
root=Path(__file__).resolve().parents[1];source=root/'assets-github/variants/B';out=root/'assets-github/approved';out.mkdir(parents=True,exist_ok=True)
bg='#14243e';muted='#c1d2e8';regular='/System/Library/Fonts/Supplemental/Arial.ttf'
def background(w,h):
 """Render a restrained blue glow and grid, corresponding to the SVG master."""
 im=Image.new('RGB',(w,h));pixels=im.load()
 for y in range(h):
  for x in range(w):
   nx=x/w;ny=y/h
   glow=max(0,1-math.hypot((nx-.72)/.82,(ny-.22)/1.1))*.65
   lower=max(0,1-math.hypot((nx-.08)/.72,(ny-.95)/1.2))*.24
   light=glow+lower
   pixels[x,y]=(round(20+10*light),round(36+20*light),round(62+28*light))
 overlay=Image.new('RGBA',(w,h));d=ImageDraw.Draw(overlay);step=max(16,round(48*h/640))
 for x in range(0,w,step):d.line((x,0,x,h),fill=(139,196,255,9),width=1)
 for y in range(0,h,step):d.line((0,y,w,y),fill=(139,196,255,9),width=1)
 return Image.alpha_composite(im.convert('RGBA'),overlay).convert('RGB')
def svg_background(w):
 return f'''<defs><radialGradient id="glow" cx="72%" cy="22%" r="82%"><stop stop-color="#1e385a" stop-opacity=".65"/><stop offset="1" stop-color="#14243e" stop-opacity="0"/></radialGradient><radialGradient id="lower" cx="8%" cy="95%" r="72%"><stop stop-color="#1e385a" stop-opacity=".24"/><stop offset="1" stop-color="#14243e" stop-opacity="0"/></radialGradient><pattern id="grid" width="48" height="48" patternUnits="userSpaceOnUse"><path d="M48 0H0V48" fill="none" stroke="#8bc4ff" stroke-opacity=".035" stroke-width="1"/></pattern></defs><rect width="{w}" height="640" fill="{bg}"/><rect width="{w}" height="640" fill="url(#glow)"/><rect width="{w}" height="640" fill="url(#lower)"/><rect width="{w}" height="640" fill="url(#grid)"/>'''
for lang in ['en','de']:
 original=Image.open(source/f'banner-{lang}.png').convert('RGB');d=ImageDraw.Draw(original);d.rectangle((60,560,700,615),fill=bg);d.text((66,570),'deckerweb  ·  QuickNav',font=ImageFont.truetype(regular,18),fill=muted)
 # The foreground is the unchanged approved composition, with an opaque selected row.
 mask=ImageChops.difference(original,Image.new('RGB',original.size,bg)).convert('L').point(lambda v:255 if v else 0);ImageDraw.Draw(mask).rectangle((784,244,1181,290),fill=255)
 foreground=original.convert('RGBA');foreground.putalpha(mask)
 im=background(1280,640);im.paste(foreground,(0,0),foreground);im.save(out/f'banner-{lang}.png')
 svg=(source/f'banner-{lang}.svg').read_text();body=svg[svg.index('>')+1:svg.rindex('</svg>')];body=re.sub(r'<title>.*?</title>','',body);body=body.replace('<rect width="1280" height="640" fill="#14243e"/>','',1);body=body.replace(('Artwork proposal ' if lang=='en' else 'Grafikentwurf ')+'B','QuickNav')
 (out/f'banner-{lang}.svg').write_text(f'<svg xmlns="http://www.w3.org/2000/svg" width="1280" height="640" viewBox="0 0 1280 640"><title>Oxygen QuickNav</title>{svg_background(1280)}{body}</svg>\n')
 for w,h in [(772,250),(1544,500)]:
  im=background(w,h);scale=h/640;size=(round(1280*scale),h);fg=foreground.resize(size,Image.Resampling.LANCZOS);im.paste(fg,((w-size[0])//2,0),fg);im.save(out/f'wp-banner-{lang}-{w}x{h}.png')
 wide=772/250*640;offset=(wide-1280)/2
 (out/f'wp-banner-{lang}.svg').write_text(f'<svg xmlns="http://www.w3.org/2000/svg" width="1544" height="500" viewBox="0 0 {wide:.2f} 640"><title>Oxygen QuickNav</title>{svg_background(wide)}<g transform="translate({offset:.2f} 0)">{body}</g></svg>\n')
# Apply the same restrained background to the icon's original vector foreground.
icon=Image.open(source/'icon-256.png').convert('RGB')
mask=ImageChops.difference(icon,Image.new('RGB',icon.size,bg)).convert('L').point(lambda v:255 if v else 0)
foreground=icon.convert('RGBA');foreground.putalpha(mask)
icon=background(256,256).convert('RGBA');icon.alpha_composite(foreground)
rounded=Image.new('L',(1024,1024));ImageDraw.Draw(rounded).rounded_rectangle((0,0,1023,1023),radius=160,fill=255)
icon.putalpha(rounded.resize((256,256),Image.Resampling.LANCZOS))
icon.save(out/'icon-256.png');icon.resize((128,128),Image.Resampling.LANCZOS).save(out/'icon-128.png')
svg=(source/'icon.svg').read_text();body=svg[svg.index('>')+1:svg.rindex('</svg>')];body=body.replace('<rect width="256" height="256" rx="40" fill="#14243e"/>','',1)
(out/'icon.svg').write_text('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><title>Oxygen QuickNav</title><defs><clipPath id="rounded"><rect width="256" height="256" rx="40"/></clipPath></defs><g clip-path="url(#rounded)"><g transform="scale(.4)">'+svg_background(640)+'</g>'+body+'</g></svg>\n')
brand=root/'assets/brand';brand.mkdir(exist_ok=True)
for name in ['icon.svg','icon-128.png','icon-256.png']:shutil.copy2(out/name,brand/name)
(root/'assets-github/design.json').write_text(json.dumps({'approved_variant':'B','approved_on':'2026-10-08','refinement':'One shared banner composition with restrained blue glow and fine grid; wide formats retain every foreground element; icon background uses the same glow/grid','copyright':'2026 David Decker – DECKERWEB','license':'GPL-2.0-or-later','source':'Original vector artwork; drafts retained under variants','fonts':'System Arial references only; no font binaries distributed'},indent=2)+'\n')
print('One approved composition for every banner; subtle glow and grid background.')
