postgres:
  image: postgres:18
  env:
    POSTGRES_PASSWORD: postgres
  ports:
    - 5432:5432
