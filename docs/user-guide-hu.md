# Szorzós színező — Felhasználói útmutató (Magyar)

Ez az útmutató bemutatja, hogyan készíthetsz nyomtatható szorzás- és osztásfeladatokat egy kis pixelképből. Tanároknak szól, akik 6–7 éves körüli gyerekekkel dolgoznak.

## Bevezetés

A **Szorzós színező** egy képet 10×10-es színezős ráccssá alakít. Minden színes mező a rácson egy feladatot ad a **sora**, **oszlopa** és **színe** alapján.

A diákok a rács segítségével olvassák ki a sor- és oszlopszámokat, majd rövid szorzás- vagy osztásfeladatokat oldanak meg. A munkalap nyomtatásra kész: színtáblázat, feladatlista és megoldókulcs is jár hozzá.

Az alkalmazás **magyar** és **angol** nyelven használható.

## Első lépések

1. Nyisd meg a Szorzós színező weboldalt a böngészőben.
2. Az oldal tetején válaszd ki a nyelvet:
   - **Magyar**
   - **English** (angol)
3. A további szövegek (gombok, címkék, üzenetek) a választott nyelven jelennek meg.

A nyelvet bármikor megváltoztathatod a legördülő menüből. Az oldal újratöltődik az új nyelven.

## Kép feltöltése

1. Kattints a **Kép kiválasztása** gombra, és válassz egy képet a számítógépedről.
2. Opcionális beállítások (alapértelmezetten mindkettő be van kapcsolva, ajánlott):
   - **Kontraszt növelése** — tisztább színeket ad a kicsinyített 10×10-es rácsban.
   - **Élek élesítése** — segít elkülöníteni a formákat, amikor a kép lekicsinyítődik.
3. Kattints a **Kép betöltése és szerkesztő megnyitása** gombra.

### Támogatott fájltípusok

- JPEG
- PNG
- WebP
- GIF

### Tippek a jó eredményhez

- Az egyszerű, markáns formák működnek a legjobban (arcok, ikonok, betűk, egyszerű tárgyak).
- Kerüld a nagyon részletes fotókat; a finom részletek elvesznek a 10×10-es rácsban.
- Az erős színkülönbségek segítenek, hogy az alkalmazás tiszta színpalettát válasszon.

## Pixel szerkesztő

A feltöltés után megnyílik a **Pixel szerkesztő** a képből készült 10×10-es ráccsal.

### Eszköztár

- **Háttér: fehér** — a fehér mezők nem kerülnek feladatra. A munkalapon üresen maradnak.
- **Radír (fehér)** — kattints a radírra, majd fehérre festheted a mezőket.
- **Új szín** — új szín hozzáadása a palettához (legfeljebb 7 szín összesen).

### Színpaletta

Minden színhez tartozik:

- Egy **színes négyzet** — kattints rá a festéshez.
- Egy **Módosít** gomb — megnyitja a színválasztót az adott palettaszín szerkesztéséhez.

Ha megváltoztatsz egy palettaszínt, az összes azt a színt használó mező automatikusan frissül.

### Festés a rácson

1. Kattints egy színre a palettán a kiválasztáshoz.
2. Kattints a rácson a mezőkre a festéshez.
3. Kattints és húzz (vagy érints és húzz táblagépen) több mező gyors festéséhez.

Legalább egy nem fehér mezőt festeni kell a feladatok generálása előtt.

## A rács értelmezése

A munkalap rácsa két számsort használ:

| Szín | Jelentés |
|------|----------|
| **Kék számok** (bal oldal) | **Sor** számok (1–10) |
| **Zöld számok** (felül) | **Oszlop** számok (1–10) |
| **Fehér mezők** | Háttér — nincs hozzá feladat |

Egy színes mező helyének megtalálásához: olvasd le a **kék sor számát** a sorában és a **zöld oszlop számát** felette.

Példa: egy piros mező a 3. sorban és a 4. oszlopban ott van, ahol a 3. sor és a 4. oszlop találkozik.

## Feladatok generálása

A pixel szerkesztő alján:

1. Válaszd ki a **Kérdés típusa** beállítást:
   - **Vegyes** — váltakozva szorzás és osztás feladatok.
   - **Csak szorzás** — minden feladat szorzás.
   - **Csak osztás** — minden feladat osztás.
2. Kattints a **Feladatok generálása a szerkesztett rácsból** gombra.

Minden **nem fehér pixel** egy feladatot ad. Ha a rácson nincs színes mező, az alkalmazás hibát jelez, és legalább egy színes mezőt kér.

## Hogyan működnek a feladatok

A feladatoknál a hiányzó **sor** vagy **oszlop** számát kell megtalálni — nem a szorzat végredményét.

A hiányzó érték egy **beírható mező**:

- **Világoskék mező sötétkék kerettel** — ide a **sor** számát kell írni.
- **Világoszöld mező sötétzöld kerettel** — ide az **oszlop** számát kell írni.

Az ismert sor számok **kék színnel**, az ismert oszlop számok **zöld színnel** jelennek meg.

### Szorzás példák

| Amit a diák lát | Mit kell megtalálni |
|-----------------|---------------------|
| `[kék mező] × 4 = 12` | Sor szám (3) |
| `3 × [zöld mező] = 12` | Oszlop szám (4) |

### Osztás példák

| Amit a diák lát | Mit kell megtalálni |
|-----------------|---------------------|
| `12 ÷ [kék mező] = 4` | Sor szám (3) |
| `12 ÷ 3 = [zöld mező]` | Oszlop szám (4) |

### Színjelölő

Minden feladat végén egy kis **színjelölő** (színes négyzet + szám) található. Ez egyezik a munkalap színtáblázatával, és megmutatja, **melyik mezőhöz** tartozik a feladat a rácson.

A diákok:

1. Megkeresik a megfelelő színt a rácson a jelölő alapján.
2. Kiolvassák annak a mezőnek a sor (kék) és oszlop (zöld) számát.
3. Ezekkel az számokkal oldják meg az egyenletet.

A feladatok három oszlopban, egymástól távolabb jelennek meg, hogy könnyen olvashatók és írhatók legyenek.

## Nyomtatás

1. A feladatok generálása után görgess le a **Munkalap** részhez.
2. Kattints a **Nyomtatási előnézet** gombra a böngésző nyomtatási ablakának megnyitásához.
3. A nyomtatás tartalmazza:
   - **1. oldal — Munkalap:** a 10×10-es rács, színtáblázat és feladatok (megoldások nélkül).
   - **2. oldal — Megoldások:** előnézeti kép és a teljes megoldókulcs tanároknak.

A böngésző nyomtatási beállításaiban választhatod a papírméretet, margókat, és hogy mindkét oldalt nyomtasd-e.

A képernyőn látható **Nyomtatási előnézet** gomb magán a nyomtatott oldalon nem jelenik meg.

## Tippek és hibaelhárítás

### Általános tippek

- Kezdj egyszerű képpel, és szükség esetén finomítsd a színeket a szerkesztőben.
- Legfeljebb **7 szín** használható a fehér háttéren kívül.
- Kevesebb szín gyakran áttekinthetőbb munkalapot ad kisiskolásoknak.
- A vegyes kérdéstípus változatosságot ad; a csak szorzás vagy csak osztás mód akkor jó, ha egy készséget gyakorolsz.

### Gyakori üzenetek

| Üzenet | Teendő |
|--------|--------|
| *Legalább egy színes pixelt adj hozzá a feladatok generálása előtt.* | Fess legalább egy mezőt palettaszínnel (ne fehérrel). |
| *Kérjük, érvényes képfájlt töltsön fel.* | Válassz fájlt, és küldd el újra az űrlapot. |
| *A feltöltött fájl nem érvényes kép.* | Használj szabványos JPEG, PNG, WebP vagy GIF fájlt. |
| *A fájlt nem sikerült tárolni.* | Próbáld újra; ha továbbra is fennáll, jelezd a weboldal üzemeltetőjének. |

### Az Új szín gomb inaktív

A paletta legfeljebb **7 színt** tartalmazhat. Ha már 7 szín van, az **Új szín** gomb szürkén, inaktívan jelenik meg. Használj kevesebb színt, vagy a meglévőket a **Módosít** gombbal állítsd.

---

*Technikai telepítéshez lásd a fő [README](../README.md) fájlt. Angol útmutató: [User Guide (EN)](user-guide-en.md). Tanulóknak: [Tanulói útmutató (HU)](../?student_guide=1&lang=hu).*
