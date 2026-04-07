import openpyxl, pandas as pd, math, warnings, numpy as np
warnings.filterwarnings('ignore')

pagalo_path = r"C:\Users\alejandro.martinez\Downloads\Reporte_de_Pagos_2026-03-01_a_2026-03-31.xlsx"
aqua_path   = r"C:\Users\alejandro.martinez\Downloads\AquaCarwash (2).xlsx"

# ===============================================================
# 1. LOAD PAGALO
# ===============================================================
p_raw = pd.read_excel(pagalo_path, sheet_name='ReportePagos', header=None, dtype=str)
p_raw.columns = range(len(p_raw.columns))
header_idx = p_raw[p_raw[0] == 'ID'].index[0]
p = p_raw.iloc[header_idx+1:].copy()
p.columns = p_raw.iloc[header_idx].tolist()
p.columns = [str(c).strip() for c in p.columns]
p = p.reset_index(drop=True)

col_map_p = {}
for c in p.columns:
    if 'Num' in c or 'Operaci' in c:
        col_map_p[c] = 'Num Operacion'
p = p.rename(columns=col_map_p)
p = p.dropna(how='all')

p['Fecha'] = pd.to_datetime(p['Fecha'], errors='coerce')
p['Hora']  = p['Hora'].astype(str).str.strip()
p['Num Operacion']  = pd.to_numeric(p['Num Operacion'], errors='coerce')
p['Monto Efectivo'] = pd.to_numeric(p['Monto Efectivo'], errors='coerce').fillna(0)
p['Monto Tarjeta']  = pd.to_numeric(p['Monto Tarjeta'],  errors='coerce').fillna(0)
p['Monto Total']    = p['Monto Efectivo'] + p['Monto Tarjeta']

# ===============================================================
# 2. LOAD AQUACARWASH
# ===============================================================
wb = openpyxl.load_workbook(aqua_path, data_only=True)
ws = wb['Sheet1']
all_rows, headers = [], None
for i, row in enumerate(ws.iter_rows(values_only=True)):
    cleaned = [None if (isinstance(v, float) and (math.isinf(v) or math.isnan(v))) else v for v in row]
    if i == 2:
        headers = cleaned
    elif i > 2:
        all_rows.append(cleaned)
a = pd.DataFrame(all_rows, columns=headers)
a.columns = [str(c).strip() for c in a.columns]
col_map_a = {}
for c in a.columns:
    if 'Tipo' in c or 'transacci' in c:
        col_map_a[c] = 'Tipo Transaccion'
a = a.rename(columns=col_map_a)
a = a.dropna(how='all')
a['Fecha'] = pd.to_datetime(a['Fecha'], errors='coerce')
a['Hora']  = a['Hora'].astype(str).str.strip()
a['Id']    = pd.to_numeric(a['Id'], errors='coerce')
a['Total'] = pd.to_numeric(a['Total'], errors='coerce').fillna(0)

# ===============================================================
# 3. FILTER MARCH 13, 2026
# ===============================================================
TARGET = pd.Timestamp('2026-03-13')
p13      = p[p['Fecha'] == TARGET].copy()
a13      = a[a['Fecha'] == TARGET].copy()
a13_pos  = a13[a13['Total'] > 0].copy()

SEP = "=" * 72

print(SEP)
print("  MARCH 13, 2026 -- TRANSACTION ANALYSIS")
print(SEP)

# --- Task 2: Counts ---------------------------------------------------------
print("\n" + "-"*72)
print("TASK 2 -- TRANSACTION COUNTS")
print("-"*72)
print(f"  Pagalo (File 1)          : {len(p13):>6} transactions")
print(f"  AquaCarwash ALL rows     : {len(a13):>6}")
print(f"  AquaCarwash (Total > 0)  : {len(a13_pos):>6} transactions")

# --- Task 3: Totals ---------------------------------------------------------
print("\n" + "-"*72)
print("TASK 3 -- TOTAL AMOUNTS")
print("-"*72)
p_efectivo = p13['Monto Efectivo'].sum()
p_tarjeta  = p13['Monto Tarjeta'].sum()
p_total    = p13['Monto Total'].sum()
a_total    = a13_pos['Total'].sum()
print(f"  Pagalo -- Monto Efectivo : ${p_efectivo:>10,.2f}")
print(f"  Pagalo -- Monto Tarjeta  : ${p_tarjeta:>10,.2f}")
print(f"  Pagalo -- TOTAL          : ${p_total:>10,.2f}")
print(f"  AquaCarwash -- TOTAL     : ${a_total:>10,.2f}")
print(f"  Difference (Pagalo-Aqua) : ${p_total - a_total:>10,.2f}")

# --- Task 4: Payment method -------------------------------------------------
print("\n" + "-"*72)
print("TASK 4 -- COUNT BY PAYMENT METHOD")
print("-"*72)
print("\n  [PAGALO -- Forma de Pago]")
for m, c in p13['Forma de Pago'].value_counts().items():
    print(f"    {str(m):<35}: {c:>4}")
print("\n  [AQUACARWASH -- Metodo] (Total > 0 only)")
for m, c in a13_pos['Metodo'].value_counts().items():
    print(f"    {str(m):<35}: {c:>4}")

# --- Task 5: Service/package ------------------------------------------------
print("\n" + "-"*72)
print("TASK 5 -- COUNT BY SERVICE / PACKAGE")
print("-"*72)
print("\n  [PAGALO -- Servicio]")
for s, c in p13['Servicio'].value_counts().items():
    print(f"    {str(s):<45}: {c:>4}")
print("\n  [AQUACARWASH -- Paquete] (Total > 0 only)")
for s, c in a13_pos['Paquete'].value_counts().items():
    print(f"    {str(s):<45}: {c:>4}")

# --- Task 6: Match ----------------------------------------------------------
print("\n" + "-"*72)
print("TASK 6 -- MATCHING (Num Operacion == Id)")
print("-"*72)
p_ids = set(p13['Num Operacion'].dropna().astype(int))
a_ids = set(a13['Id'].dropna().astype(int))

matched        = p_ids & a_ids
only_in_pagalo = p_ids - a_ids
only_in_aqua   = a_ids - p_ids

print(f"  Matched                          : {len(matched):>5}")
print(f"  In Pagalo but NOT in AquaCarwash : {len(only_in_pagalo):>5}")
print(f"  In AquaCarwash but NOT in Pagalo : {len(only_in_aqua):>5}")

# --- Task 7: Unmatched ------------------------------------------------------
print("\n" + "-"*72)
print("TASK 7 -- UNMATCHED TRANSACTIONS")
print("-"*72)

pd.set_option('display.max_rows', 500)
pd.set_option('display.max_colwidth', 50)
pd.set_option('display.width', 140)

print(f"\n  [PAGALO ONLY -- {len(only_in_pagalo)} transactions]")
if only_in_pagalo:
    p_unm = p13[p13['Num Operacion'].isin(only_in_pagalo)][
        ['Num Operacion','Hora','Servicio','Monto Total','Forma de Pago']
    ].sort_values('Num Operacion')
    print(p_unm.to_string(index=False))
else:
    print("  None")

print(f"\n  [AQUACARWASH ONLY -- {len(only_in_aqua)} transactions]")
if only_in_aqua:
    a_unm = a13[a13['Id'].isin(only_in_aqua)][
        ['Id','Hora','Paquete','Total','Metodo']
    ].sort_values('Id')
    print(a_unm.to_string(index=False))
else:
    print("  None")

# --- Task 8: Amount discrepancies -------------------------------------------
print("\n" + "-"*72)
print("TASK 8 -- AMOUNT DISCREPANCIES (matched transactions)")
print("-"*72)
if matched:
    p_m = p13[p13['Num Operacion'].isin(matched)][
        ['Num Operacion','Hora','Monto Total','Forma de Pago']
    ].copy().rename(columns={'Num Operacion':'Id','Monto Total':'P_Total','Hora':'P_Hora','Forma de Pago':'P_Metodo'})
    a_m = a13[a13['Id'].isin(matched)][
        ['Id','Hora','Total','Metodo']
    ].copy().rename(columns={'Total':'A_Total','Hora':'A_Hora','Metodo':'A_Metodo'})
    merged = p_m.merge(a_m, on='Id', how='inner')
    merged['Diff'] = merged['P_Total'] - merged['A_Total']
    discrepancies = merged[merged['Diff'] != 0]
    print(f"  Total matched: {len(merged)}, Discrepancies: {len(discrepancies)}")
    if len(discrepancies):
        print(discrepancies[['Id','P_Hora','A_Hora','P_Total','A_Total','Diff','P_Metodo','A_Metodo']].to_string(index=False))
    else:
        print("  All matched amounts are identical.")

# --- Task 9: Suspicious patterns --------------------------------------------
print("\n" + "-"*72)
print("TASK 9 -- SUSPICIOUS PATTERNS")
print("-"*72)

# 9a. Duplicate Num Operacion in Pagalo
print("\n  [9a] Pagalo -- Duplicate Num Operacion on March 13:")
dup_p = p13[p13['Num Operacion'].duplicated(keep=False)].sort_values('Num Operacion')
if len(dup_p):
    print(dup_p[['Num Operacion','Hora','Servicio','Monto Total','Forma de Pago']].to_string(index=False))
else:
    print("  None detected.")

# 9b. Duplicate Id in AquaCarwash
print("\n  [9b] AquaCarwash -- Duplicate Id on March 13:")
dup_a = a13[a13['Id'].duplicated(keep=False)].sort_values('Id')
if len(dup_a):
    print(dup_a[['Id','Hora','Paquete','Total','Metodo']].to_string(index=False))
else:
    print("  None detected.")

# 9c. Gaps in Num Operacion
sorted_ops = sorted(p13['Num Operacion'].dropna().astype(int).tolist())
gaps = []
for i in range(1, len(sorted_ops)):
    d = sorted_ops[i] - sorted_ops[i-1]
    if d > 1:
        gaps.append((sorted_ops[i-1], sorted_ops[i], d-1))
print(f"\n  [9c] Pagalo -- Gaps in Num Operacion: {len(gaps)} gap(s)")
for g in gaps:
    print(f"    After {g[0]} -> Next {g[1]}  (missing {g[2]})")
if not gaps:
    print("  No gaps.")

# 9d. Zero-amount in Pagalo
zero_p = p13[p13['Monto Total'] == 0]
print(f"\n  [9d] Pagalo -- Zero-amount transactions: {len(zero_p)}")
if len(zero_p):
    print(zero_p[['Num Operacion','Hora','Servicio','Monto Total','Forma de Pago']].to_string(index=False))

# 9e. Zero/negative in AquaCarwash
zero_a = a13[a13['Total'] <= 0]
print(f"\n  [9e] AquaCarwash -- Zero/negative Total on March 13: {len(zero_a)}")
if len(zero_a):
    print(zero_a[['Id','Hora','Paquete','Total','Metodo']].to_string(index=False))

# 9f. Outside business hours
def get_hour(t):
    try: return int(str(t).split(':')[0])
    except: return None

p13c = p13.copy()
p13c['Hour'] = p13c['Hora'].apply(get_hour)
odd_p = p13c[(p13c['Hour'].notna()) & ((p13c['Hour'] < 7) | (p13c['Hour'] >= 22))]
print(f"\n  [9f] Pagalo -- Outside 07:00-22:00: {len(odd_p)}")
if len(odd_p):
    print(odd_p[['Num Operacion','Hora','Servicio','Monto Total']].to_string(index=False))

a13c = a13.copy()
a13c['Hour'] = a13c['Hora'].apply(get_hour)
odd_a = a13c[(a13c['Hour'].notna()) & ((a13c['Hour'] < 7) | (a13c['Hour'] >= 22))]
print(f"\n  [9g] AquaCarwash -- Outside 07:00-22:00: {len(odd_a)}")
if len(odd_a):
    print(odd_a[['Id','Hora','Paquete','Total','Metodo']].to_string(index=False))

# 9h. Time-offset check for matched
print("\n  [9h] Time-offset cross-check (Pagalo time - 1hr) for matched txns:")
if matched:
    def to_minutes(t):
        try:
            parts = str(t).split(':')
            return int(parts[0])*60 + int(parts[1])
        except:
            return None
    merged['P_min'] = merged['P_Hora'].apply(to_minutes)
    merged['A_min'] = merged['A_Hora'].apply(to_minutes)
    merged['P_adj_min'] = merged['P_min'] - 60
    merged['time_diff_min'] = merged['P_adj_min'] - merged['A_min']
    big = merged[merged['time_diff_min'].abs() > 5].sort_values('time_diff_min')
    print(f"  Matched pairs with >5 min adjusted-time gap: {len(big)}")
    if len(big):
        print(big[['Id','P_Hora','A_Hora','time_diff_min']].head(30).to_string(index=False))
    else:
        print("  All matched transactions align correctly after -1hr offset (within 5 min).")

# 9i. Largest amounts (outliers)
print("\n  [9i] Pagalo -- Top 5 highest amounts on March 13:")
print(p13[['Num Operacion','Hora','Servicio','Monto Total','Forma de Pago']].sort_values('Monto Total', ascending=False).head(5).to_string(index=False))

print("\n  [9j] AquaCarwash -- Top 5 highest amounts on March 13 (Total>0):")
print(a13_pos[['Id','Hora','Paquete','Total','Metodo']].sort_values('Total', ascending=False).head(5).to_string(index=False))

print("\n" + SEP)
print("  END OF ANALYSIS")
print(SEP)
