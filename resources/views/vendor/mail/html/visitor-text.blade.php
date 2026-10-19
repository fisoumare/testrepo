{{--
    Texte libre saisi par un visiteur, affiché tel quel dans un e-mail Markdown : échappé (HTML)
    et jamais interprété comme du Markdown. Pour CommonMark, une ligne qui commence par <pre
    ouvre un bloc HTML brut qui court, lignes vides et indentation comprises, jusqu'au </pre> ;
    comme le texte est échappé, il ne peut pas contenir </pre> et donc pas refermer le bloc.
    Les retours à la ligne du visiteur sont conservés par white-space: pre-wrap.
--}}
@props(['text'])
<pre style="margin: 0 0 21px; padding: 16px; border-left: 4px solid #2d3748; background-color: #edf2f7; color: #718096; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5em; white-space: pre-wrap; overflow-wrap: anywhere;">{{ str_replace(["\r\n", "\r"], "\n", trim((string) $text)) }}</pre>
