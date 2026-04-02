# api.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from motorfisico import simular_bravo2

app = FastAPI(
    title="API Simulación Bravo II",
    description="API para ejecutar la simulación física del cohete Bravo II",
    version="1.0.0"
)


class SimulacionRequest(BaseModel):
    P_inicial_psi: float = 90.0
    Angulo_Lanzamiento: float = 90.0
    Viento_X: float = 5.0
    rho_aire: float = 1.225
    Pct_Agua_E1: float = 40.0
    Pct_Agua_E2: float = 30.0
    usar_jabon: bool = True
    M_CargaUtil: float = 0.075
    M_Seca_E1: float = 0.105
    M_Seca_E2: float = 0.1873
    Cd_E1: float = 0.6
    Cd_E2: float = 0.4
    V_descenso_meta: float = 8.0


@app.get("/")
def inicio():
    return {
        "mensaje": "API de simulación Bravo II activa",
        "endpoint_principal": "/simular",
        "metodo": "POST"
    }


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/simular")
def simular(data: SimulacionRequest):
    try:
        resultado = simular_bravo2(data.dict())
        return {
            "ok": True,
            "resultado": resultado
        }
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error al ejecutar la simulación: {str(e)}"
        )


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api:app", host="127.0.0.1", port=8000, reload=True)