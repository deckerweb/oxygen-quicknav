"""Export maintained bilingual documentation as formatted GitHub Wiki pages."""
from pathlib import Path
import argparse,json,re
root=Path(__file__).resolve().parents[1]
a=argparse.ArgumentParser();a.add_argument('destination',type=Path);a.add_argument('--templates',type=Path,required=True);args=a.parse_args();args.destination.mkdir(parents=True,exist_ok=True)
data=json.loads((root/'docs/content.json').read_text());repo='https://github.com/deckerweb/oxygen-quicknav'
pages={}
for lang,d in data.items():
 de=lang=='de';home='Startseite' if de else 'Home';start='Erste-Schritte' if de else 'Getting-started';guide='Anleitung' if de else 'User-guide';faq='Fragen-nach-Themen' if de else 'FAQ-by-topic';history='Aenderungsverlauf' if de else 'Changelog';help='Hilfe-und-Sicherheit' if de else 'Help-and-security'
 switch='[English](Home) · [Deutsch](Startseite)\n\n'
 banner='![Oxygen QuickNav]('+repo.replace('github.com','raw.githubusercontent.com')+'/main/assets-github/approved/banner-'+lang+'.png)\n\n'
 nav=' · '.join('['+label+']('+target+')' for label,target in [('Erste Schritte' if de else 'Getting started',start),('Anleitung' if de else 'User guide',guide),('Fragen nach Themen' if de else 'FAQ by topic',faq),('Änderungsverlauf' if de else 'Changelog',history),('Hilfe und Sicherheit' if de else 'Help and security',help)])
 pages[home]='# Oxygen QuickNav\n\n'+banner+switch+('Ihre Direktwege. Beide Oxygen-Generationen.' if de else 'Your shortcuts. Both Oxygen generations.')+'\n\n'+d['about']+'\n\n'+nav+'\n'
 pages[start]='# '+('Erste Schritte' if de else 'Getting started')+'\n\n'+switch+d['requirements']+'\n\n'+'\n'.join(str(n)+'. '+s for n,s in enumerate(d['install'],1))+'\n'
 pages[guide]='# '+('Anleitung' if de else 'User guide')+'\n\n'+switch+'\n'.join('- '+s for s in d['features'])+'\n'
 for basename,source in [(faq,'FAQ'),(history,'CHANGELOG')]:
  text=(root/'docs'/(source+('-de' if de else '')+'.md')).read_text();text=re.sub(r'\[(English|Deutsch)\]\([^)]*\)',switch.strip(),text,count=1);pages[basename]=text
 pages[help]='# '+('Hilfe und Sicherheit' if de else 'Help and security')+'\n\n'+switch+('[Fehler melden]('+repo+'/issues) · [Austausch]('+repo+'/discussions)\n\nSicherheitslücken vertraulich über [Private Vulnerability Reporting]('+repo+'/security/advisories/new) melden. [Sicherheitsrichtlinie]('+repo+'/blob/main/SECURITY.md).' if de else '[Report bugs]('+repo+'/issues) · [Discussions]('+repo+'/discussions)\n\nReport vulnerabilities privately using [Private Vulnerability Reporting]('+repo+'/security/advisories/new). See the [security policy]('+repo+'/blob/main/SECURITY.md).')+'\n'
values={'WIKI_HOME_EN':'Home','WIKI_HOME_DE':'Startseite','GETTING_STARTED_EN':'Getting-started','GETTING_STARTED_DE':'Erste-Schritte','USER_GUIDE_EN':'User-guide','USER_GUIDE_DE':'Anleitung','FAQ_EN':'FAQ-by-topic','FAQ_DE':'Fragen-nach-Themen','CHANGELOG_EN':'Changelog','CHANGELOG_DE':'Aenderungsverlauf','HELP_SECURITY_EN':'Help-and-security','HELP_SECURITY_DE':'Hilfe-und-Sicherheit','PLUGIN_NAME':'Oxygen QuickNav','WIKI_HOME':'Home','REPOSITORY':repo,'RELEASES':repo+'/releases','ISSUES':repo+'/issues','DISCUSSIONS':repo+'/discussions','SECURITY_POLICY':repo+'/blob/main/SECURITY.md','COPYRIGHT_YEARS':'2025–2026'}
for name in ['_Sidebar','_Footer']:
 text=(args.templates/(name+'.md')).read_text();text=re.sub(r'<!--.*?-->\s*','',text,flags=re.S)
 for key,value in values.items():text=text.replace('{{'+key+'}}',value)
 assert '{{' not in text
 pages[name]=text
for name,text in pages.items():
 for label,url in re.findall(r'(?<!!)\[([^]]+)\]\(([^)]+)\)',text):
  if not url.startswith(('https://','http://','#')):assert url.split('#')[0] in pages,(name,url)
 (args.destination/(name+'.md')).write_text(text)
print(str(len(pages))+' Wiki pages exported; local links verified; no live publication')
