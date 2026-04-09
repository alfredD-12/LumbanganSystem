# ml_models/generate_synthetic_and_insert.py
import random, mysql.connector
from datetime import date, timedelta
from faker import Faker

fake = Faker()
conn = mysql.connector.connect(host='localhost', user='root', password='', database='lumbangansystem')
cur = conn.cursor()

# Create some households/persons first (if you prefer)
# Here we only insert migrations referencing existing person ids
PERSON_COUNT = 300  # adapt to how many persons exist

for i in range(100):
    pid = random.randint(1, PERSON_COUNT)
    from_p = random.randint(1,12)
    to_p = random.randint(1,12)
    moved_at = date.today() - timedelta(days=random.randint(0, 365*10))
    reason = random.choice(['Job', 'Family', 'Study', 'Other'])
    cur.execute(
        "INSERT INTO resident_migrations (person_id, from_purok_id, to_purok_id, moved_at, reason, is_synthetic) VALUES (%s,%s,%s,%s,%s,1)",
        (pid, from_p, to_p, moved_at, reason)
    )

conn.commit()
cur.close()
conn.close()
print("Inserted synthetic migrations")
