# motorfisico.py
import numpy as np
from scipy.integrate import solve_ivp


def simular_bravo2(data: dict) -> dict:
    # ===============================
    # PARÁMETROS DE ENTRADA
    # ===============================
    P_inicial_psi = float(data.get("P_inicial_psi", 90.0))
    Angulo_Lanzamiento = float(data.get("Angulo_Lanzamiento", 90.0))
    Viento_X = float(data.get("Viento_X", 5.0))
    rho_aire = float(data.get("rho_aire", 1.225))

    Pct_Agua_E1 = float(data.get("Pct_Agua_E1", 40.0)) / 100.0
    Pct_Agua_E2 = float(data.get("Pct_Agua_E2", 30.0)) / 100.0
    usar_jabon = bool(data.get("usar_jabon", True))

    M_CargaUtil = float(data.get("M_CargaUtil", 0.075))
    M_Seca_E1 = float(data.get("M_Seca_E1", 0.105))
    M_Seca_E2 = float(data.get("M_Seca_E2", 0.1873))

    Cd_E1 = float(data.get("Cd_E1", 0.6))
    Cd_E2 = float(data.get("Cd_E2", 0.4))
    V_descenso_meta = float(data.get("V_descenso_meta", 8.0))
    solver_rtol = float(data.get("solver_rtol", 1e-8))
    solver_atol = float(data.get("solver_atol", 1e-10))

    if solver_rtol <= 0 or solver_atol <= 0:
        raise ValueError("solver_rtol y solver_atol deben ser positivos.")

    # ===============================
    # CONSTANTES FÍSICAS
    # ===============================
    g = 9.81
    rho_agua = 1000.0

    Vol_Botella = 0.002
    P_inicial_Pa = P_inicial_psi * 6894.76
    P_atm = 101325.0
    Gamma = 1.4

    Diametro_Tobera_m = 9.0 / 1000.0
    Area_Tobera = np.pi * (Diametro_Tobera_m / 2) ** 2
    Cd_Tobera = 0.95

    Diametro_Botella_m = 10.5 / 100.0
    Area_Botella = np.pi * (Diametro_Botella_m / 2) ** 2

    M_agua_E1_inicial = Vol_Botella * rho_agua * Pct_Agua_E1
    M_agua_E2_inicial = Vol_Botella * rho_agua * Pct_Agua_E2

    Masa_Despegue = (
        M_Seca_E1 + M_agua_E1_inicial + M_Seca_E2 + M_agua_E2_inicial + M_CargaUtil
    )

    # ===============================
    # ECUACIONES DE MOVIMIENTO
    # ===============================
    def ecuaciones_movimiento(t, y, Fase_Actual, Masa_Estatica_Actual):
        Vel_X, Vel_Y = y[1], y[3]
        masa_agua_actual = max(y[4], 0.0)

        masa_total = Masa_Estatica_Actual + masa_agua_actual

        dir_empuje_x = np.cos(np.deg2rad(Angulo_Lanzamiento))
        dir_empuje_y = np.sin(np.deg2rad(Angulo_Lanzamiento))

        Vx_relativa = Vel_X - Viento_X
        Vy_relativa = Vel_Y
        V_rel_mag = np.sqrt(Vx_relativa**2 + Vy_relativa**2)

        if Fase_Actual == 1:
            Area_Cd = Cd_E1 * Area_Botella
        elif Fase_Actual in [2, 3]:
            Area_Cd = Cd_E2 * Area_Botella
        else:
            Area_Cd = (2 * masa_total * g) / (rho_aire * V_descenso_meta**2)

        Arrastre_X = 0.5 * rho_aire * Area_Cd * V_rel_mag * Vx_relativa
        Arrastre_Y = 0.5 * rho_aire * Area_Cd * V_rel_mag * Vy_relativa

        Empuje = 0.0
        Flujo_Masico = 0.0

        if Fase_Actual in [1, 2] and masa_agua_actual > 0:
            Vol_Agua_Inicial = (
                M_agua_E1_inicial if Fase_Actual == 1 else M_agua_E2_inicial
            ) / 1000.0
            Vol_Aire_Inicial = Vol_Botella - Vol_Agua_Inicial
            Vol_Aire_Actual = Vol_Botella - (masa_agua_actual / 1000.0)

            P_abs_actual = (P_inicial_Pa + P_atm) * (Vol_Aire_Inicial / Vol_Aire_Actual) ** Gamma
            P_manometrica = P_abs_actual - P_atm

            if P_manometrica > 0:
                Vel_Salida = np.sqrt((2 * P_manometrica) / 1000.0)
                Flujo_Masico = Cd_Tobera * Area_Tobera * 1000.0 * Vel_Salida
                Empuje = Flujo_Masico * Vel_Salida

                if usar_jabon:
                    Empuje *= 1.05
                    Flujo_Masico *= 0.75

        Empuje_X = Empuje * dir_empuje_x
        Empuje_Y = Empuje * dir_empuje_y

        ax = (Empuje_X - Arrastre_X) / masa_total
        ay = (Empuje_Y - Arrastre_Y - (masa_total * g)) / masa_total

        return [Vel_X, ax, Vel_Y, ay, -Flujo_Masico]

    # ===============================
    # EVENTOS
    # ===============================
    def evento_agua(t, y, Fase, Masa):
        return y[4]

    evento_agua.terminal = True
    evento_agua.direction = -1

    def evento_apogeo(t, y, Fase, Masa):
        return y[3]

    evento_apogeo.terminal = True
    evento_apogeo.direction = -1

    def evento_suelo(t, y, Fase, Masa):
        return y[2]

    evento_suelo.terminal = True
    evento_suelo.direction = -1

    # ===============================
    # SIMULACIÓN POR FASES
    # ===============================
    Masa_Est_F1 = M_Seca_E1 + M_Seca_E2 + M_agua_E2_inicial + M_CargaUtil
    res1 = solve_ivp(
        ecuaciones_movimiento,
        [0, 60],
        [0, 0, 0, 0, M_agua_E1_inicial],
        args=(1, Masa_Est_F1),
        events=evento_agua,
        max_step=0.05,
        rtol=solver_rtol,
        atol=solver_atol,
    )

    y0_2 = res1.y[:, -1].copy()
    y0_2[4] = M_agua_E2_inicial
    Masa_Est_F2 = M_Seca_E2 + M_CargaUtil
    res2 = solve_ivp(
        ecuaciones_movimiento,
        [res1.t[-1], 60],
        y0_2,
        args=(2, Masa_Est_F2),
        events=evento_agua,
        max_step=0.05,
        rtol=solver_rtol,
        atol=solver_atol,
    )

    y0_3 = res2.y[:, -1].copy()
    y0_3[4] = 0
    res3 = solve_ivp(
        ecuaciones_movimiento,
        [res2.t[-1], 100],
        y0_3,
        args=(3, Masa_Est_F2),
        events=evento_apogeo,
        max_step=0.1,
        rtol=solver_rtol,
        atol=solver_atol,
    )

    y0_4 = res3.y[:, -1].copy()
    y0_4[1] = Viento_X
    y0_4[3] = -V_descenso_meta
    res4 = solve_ivp(
        ecuaciones_movimiento,
        [res3.t[-1], 300],
        y0_4,
        args=(4, Masa_Est_F2),
        events=evento_suelo,
        max_step=0.5,
        rtol=solver_rtol,
        atol=solver_atol,
    )

    # ===============================
    # UNIÓN DE RESULTADOS
    # ===============================
    t_tot = np.concatenate((res1.t, res2.t[1:], res3.t[1:], res4.t[1:]))
    X_tot = np.concatenate((res1.y[0], res2.y[0, 1:], res3.y[0, 1:], res4.y[0, 1:]))
    Vx_tot = np.concatenate((res1.y[1], res2.y[1, 1:], res3.y[1, 1:], res4.y[1, 1:]))
    Y_tot = np.concatenate((res1.y[2], res2.y[2, 1:], res3.y[2, 1:], res4.y[2, 1:]))
    Vy_tot = np.concatenate((res1.y[3], res2.y[3, 1:], res3.y[3, 1:], res4.y[3, 1:]))

    Vel_Mag = np.sqrt(Vx_tot**2 + Vy_tot**2)
    Ax_tot = np.gradient(Vx_tot, t_tot)
    Ay_tot = np.gradient(Vy_tot, t_tot)
    Accel_Mag = np.sqrt(Ax_tot**2 + Ay_tot**2)

    apogeo = float(np.max(Y_tot))
    deriva = float(X_tot[-1])

    t_sep1 = float(res1.t[-1])
    t_sep2 = float(res2.t[-1])
    t_apog = float(res3.t[-1])

    idx1 = int(np.searchsorted(t_tot, t_sep1))
    idx2 = int(np.searchsorted(t_tot, t_sep2))
    idx_apog = int(np.searchsorted(t_tot, t_apog))

    return {
        "metricas": {
            "apogeo": apogeo,
            "deriva": deriva,
            "velocidad_max": float(np.max(Vel_Mag)),
            "masa_despegue": float(Masa_Despegue),
        },
        "eventos": {
            "t_sep1": t_sep1,
            "t_sep2": t_sep2,
            "t_apog": t_apog,
            "idx_sep1": idx1,
            "idx_sep2": idx2,
            "idx_apog": idx_apog,
        },
        "series": {
            "t": t_tot.tolist(),
            "x": X_tot.tolist(),
            "y": Y_tot.tolist(),
            "vx": Vx_tot.tolist(),
            "vy": Vy_tot.tolist(),
            "velocidad": Vel_Mag.tolist(),
            "aceleracion": Accel_Mag.tolist(),
        },
        "solver": {
            "rtol": solver_rtol,
            "atol": solver_atol,
        },
    }


if __name__ == "__main__":
    parametros_demo = {
        "P_inicial_psi": 90.0,
        "Angulo_Lanzamiento": 90.0,
        "Viento_X": 5.0,
        "rho_aire": 1.225,
        "Pct_Agua_E1": 40.0,
        "Pct_Agua_E2": 30.0,
        "usar_jabon": True,
        "M_CargaUtil": 0.075,
        "M_Seca_E1": 0.105,
        "M_Seca_E2": 0.1873,
        "Cd_E1": 0.6,
        "Cd_E2": 0.4,
        "V_descenso_meta": 8.0,
    }

    resultado = simular_bravo2(parametros_demo)

    print("Apogeo:", resultado["metricas"]["apogeo"])
    print("Deriva:", resultado["metricas"]["deriva"])
    print("Velocidad máxima:", resultado["metricas"]["velocidad_max"])
    print("Masa de despegue:", resultado["metricas"]["masa_despegue"])
