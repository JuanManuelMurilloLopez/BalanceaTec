#include <LiquidCrystal.h>
#include "DHT.h"
#include <Wire.h>

// Configuración del DHT11
#define DHTPIN 4 // Pin de datos del DHT11
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// Configuración del LCD
LiquidCrystal lcd(22, 23, 5, 18, 19, 21);

// Configuración del MPU6050
const int MPU_ADDR = 0x68; // Dirección I2C del MPU6050
int16_t AcX, AcY, AcZ;     // Aceleración en X, Y, Z
float baseAngleX = 0, baseAngleY = 0, baseAngleZ = 0; // Referencias iniciales

// Configuración del Motor
#define MOTOR_IN1 2  // Pin IN1 del L293D
#define MOTOR_IN2 14 // Pin IN2 del L293D

void setup() {
  // Inicialización serial
  Serial.begin(9600);

  // Inicialización del DHT11
  dht.begin();
  Serial.println("DHT11 inicializado");

  // Inicialización del LCD
  lcd.begin(16, 2); // LCD de 16 columnas y 2 filas
  lcd.print("Iniciando...");
  delay(2000);
  lcd.clear();

  // Inicialización del MPU6050 con pines personalizados
  Wire.begin(25, 26); // SDA = GPIO 25, SCL = GPIO 26
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x6B); // Registro de power management
  Wire.write(0);    // Activar el MPU6050
  Wire.endTransmission(true);

  // Configuración de pines del motor
  pinMode(MOTOR_IN1, OUTPUT);
  pinMode(MOTOR_IN2, OUTPUT);
  digitalWrite(MOTOR_IN1, LOW);
  digitalWrite(MOTOR_IN2, LOW);

  // Calcular ángulos base para inicializar en 0 grados
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x3B); // Primer registro de datos (AcX)
  Wire.endTransmission(false);
  Wire.requestFrom(MPU_ADDR, 6, true);

  int16_t baseAcX = Wire.read() << 8 | Wire.read();
  int16_t baseAcY = Wire.read() << 8 | Wire.read();
  int16_t baseAcZ = Wire.read() << 8 | Wire.read();

  baseAngleX = atan2(baseAcY, baseAcZ) * 180 / PI;
  baseAngleY = atan2(baseAcX, baseAcZ) * 180 / PI;
  baseAngleZ = atan2(baseAcX, baseAcY) * 180 / PI;

  Serial.println("Ángulos inicializados en 0 grados:");
  Serial.print("Base AngleX: ");
  Serial.println(baseAngleX);
  Serial.print("Base AngleY: ");
  Serial.println(baseAngleY);
  Serial.print("Base AngleZ: ");
  Serial.println(baseAngleZ);
}

void loop() {
  // Leer temperatura y humedad
  float temperatura = dht.readTemperature();
  float humedad = dht.readHumidity();

  // Validar lecturas del DHT11
  if (isnan(temperatura) || isnan(humedad)) {
    Serial.println("Error al leer del DHT11");
    lcd.clear();
    lcd.print("Error lectura");
    return;
  }

  //Leer datos del MPU6050
  Wire.beginTransmission(MPU_ADDR);
  Wire.write(0x3B); // Primer registro de datos (AcX)
  Wire.endTransmission(false);
  Wire.requestFrom(MPU_ADDR, 6, true);

  AcX = Wire.read() << 8 | Wire.read();
  AcY = Wire.read() << 8 | Wire.read();
  AcZ = Wire.read() << 8 | Wire.read();

  // Calcular ángulos de inclinación con referencia a los valores base
  float angleX = atan2(AcY, AcZ) * 180 / PI - baseAngleX;
  float angleY = atan2(AcX, AcZ) * 180 / PI - baseAngleY;

  // Controlar motor según el ángulo
  if (angleX > 10) {
    // Gira en una dirección
    digitalWrite(MOTOR_IN1, HIGH);
    digitalWrite(MOTOR_IN2, LOW);
  } else if (angleX < -10) {
    // Gira en la dirección opuesta
    digitalWrite(MOTOR_IN1, LOW);
    digitalWrite(MOTOR_IN2, HIGH);
  } else {
    // Detiene el motor
    digitalWrite(MOTOR_IN1, LOW);
    digitalWrite(MOTOR_IN2, LOW);
  }

  // Mostrar datos en el LCD
  lcd.setCursor(0, 0); // Primera fila
  lcd.print("Temp: ");
  lcd.print(temperatura);
  lcd.print(" C");

  lcd.setCursor(0, 1); // Segunda fila
  lcd.print("Hum: ");
  lcd.print(humedad);
  lcd.print(" %");

  // Mostrar datos del MPU6050 en el monitor serial
  Serial.print("AngleX: ");
  Serial.print(angleX);
  Serial.print(" | AngleY: ");
  Serial.println(angleY);

  delay(500); // Actualizar cada 500 ms
}