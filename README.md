

## Routes

GET matieres

- GET `/matieres`
    - Response: `array(Matiere)`
    ```json
    [
      {
        "id": int,
        "nom": string,
        "idModule": int,
        "coefficient": int,
        "idSessions": [int]
      },
    ]
    ```

- POST `/matieres`
    - Body:
    ```json
    {
        "nom":string,
        "coefficient":int,
        "idModule":int
    }
    ```
  - Response:
  ```json
    {
        "status": "Matière ajoutée !"
    }
  ```
- GET `/matieres/{id:int}`
    - Response:`Matiere`
    ```json
    {
        "id": int,
        "nom": string,
        "idModule": int,
        "coefficient": int,
        "idSessions": [int]
    }
    ```
---    
- GET `/sessions`
    - Response:`array(Session)`
    ```json
    [
      {
        "id":int
        "obligatoire":boolean,
        "type":string,
        "dateDebut":"YYYY-MM-DDThh:mm:ss+hhmm",
        "dateFin":"YYYY-MM-DDThh:mm:ss+hhmm",
        "idMatiere":int,
        "nomMatiere":string
      }
    ]
    ```
- POST `/sessions`
    - Body:
    ```json
    {
        "obligatoire":boolean,
        "type":string,
        "dateDebut":"YYYY-MM-DDThh:mm:ss+hhmm",
        "dateFin":"YYYY-MM-DDThh:mm:ss+hhmm",
        "idMatiere":int
    }
    ```
    - Response:
    ```json
    {
        "status": "Session ajoutée !"
    }
    ```
    - Sessionstype :
        - "none"
        - "cours"
        - "conference"
        - "td"
        - "tp"
        - "examen"
        - "autre" 

- GET `/sessions/{id:int}`
    - Response:`Session`
    ```json
    {
        "id":int
        "obligatoire":boolean,
        "type":string,
        "dateDebut":"YYYY-MM-DDThh:mm:ss+hhmm",
        "dateFin":"YYYY-MM-DDThh:mm:ss+hhmm",
        "idMatiere":int,
        "nomMatiere":string
    }
    ```
---
- GET `/modules`
    - Response: `array(Module)`
    ```json
    [
      {
        "id": int,
        "nom": string,
        "idMatieres": [int],
        "ects": int,
        "idSemestre": int,
        "nomSemestre": string
      },
    ]
    ```
- POST `/modules`
    - Body:
    ```json
    {
        "nom":string,
        "idSemestre":int,
        "ects":int
    }
    ```
    - Response:
    ```json
    {
        "status": "Module ajouté !"
    }
    ```
- GET `/modules/{id:int}`
    - Response: `Module`
    ```json
    {
        "id": int,
        "nom": string,
        "idMatieres": [int],
        "ects": int,
        "idSemestre": int,
        "nomSemestre": string
    }
    ```
---
- GET `/sessions/promo/{id:int}/week/{YYYYMMDD:?}`
    - `YYYYMMDD = 20201127`
    - Response:`array(Session)`
    ```json
    [
      {
        "id":int
        "obligatoire":boolean,
        "type":string,
        "dateDebut":"YYYY-MM-DDThh:mm:ss+hhmm",
        "dateFin":"YYYY-MM-DDThh:mm:ss+hhmm",
        "idMatiere":int,
        "nomMatiere":string
      }
    ]
    ```
---
- GET `/promos/{id:int}/semestres`
    - Response: `array(Semestre)`
    ```json
    [
        {
            "id": int,
            "nom": string,
            "idPromotion": int,
            "nomPromotion": string,
            "nomFormation": string,
            "idModules": [int]
        }
    ]
    ```
---
- GET `/assistants/{id:int}/promos`
    - Response: `array(Promotion)`
    ```json
    [
        {
            "id": int,
            "nom": string,
            "idFormation": int,
            "nomFormation": string
        }
    ]
    ```
- GET `/assistants/{id:int}`
    - Response: `Assistant`
    ```json
    {
        "id": int,
        "nom": string,
        "prenom": string,
        "email": string,
        "numeroTel": string
    }
    ```
- GET `/assistants`
    - Response: `array(Assistant)`
    ```json
    [
        {
            "id": int,
            "nom": string,
            "prenom": string,
            "email": string,
            "numeroTel": string
        }
    ]
    ```
