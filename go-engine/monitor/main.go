package main

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"strconv"
	"strings"
	"time"

	"github.com/gorilla/websocket"
	"github.com/shirou/gopsutil/v3/cpu"
	"github.com/shirou/gopsutil/v3/host"
	"github.com/shirou/gopsutil/v3/mem"
	"github.com/shirou/gopsutil/v3/process"
)

type SystemStats struct {
	CPUUsage   float64 `json:"cpu_usage"`
	CPUTemp    float64 `json:"cpu_temp"`
	RAMUsage   float64 `json:"ram_usage"`
	RAMUsedMB  uint64  `json:"ram_used_mb"`
	RAMTotalMB uint64  `json:"ram_total_mb"`
	TotalProcs int     `json:"total_procs"`
	Battery    string  `json:"battery"`
	Uptime     string  `json:"uptime"`
	HostName   string  `json:"hostname"`
	Platform   string  `json:"platform"`
}

var upgrader = websocket.Upgrader{
	CheckOrigin: func(r *http.Request) bool { return true },
}

// Helper: Ambil suhu CPU Linux tanpa shell_exec
func getCPUTemp() float64 {
	data, err := os.ReadFile("/sys/class/thermal/thermal_zone0/temp")
	if err != nil {
		return 0.0
	}
	rawTemp, err := strconv.ParseFloat(strings.TrimSpace(string(data)), 64)
	if err != nil {
		return 0.0
	}
	return rawTemp / 1000.0
}

// Helper: Ambil kapasitas Baterai
func getBatteryCapacity() string {
	data, err := os.ReadFile("/sys/class/power_supply/BAT0/capacity")
	if err != nil {
		return "N/A"
	}
	return strings.TrimSpace(string(data)) + "%"
}

// Helper: Format Uptime persis seperti `uptime -p`
func formatUptime(uptimeSeconds uint64) string {
	days := uptimeSeconds / (24 * 3600)
	hours := (uptimeSeconds % (24 * 3600)) / 3600
	minutes := (uptimeSeconds % 3600) / 60

	var parts []string
	if days > 0 {
		parts = append(parts, fmt.Sprintf("%d days", days))
	}
	if hours > 0 {
		parts = append(parts, fmt.Sprintf("%d hours", hours))
	}
	if minutes > 0 {
		parts = append(parts, fmt.Sprintf("%d minutes", minutes))
	}
	if len(parts) == 0 {
		return "just started"
	}
	return strings.Join(parts, ", ")
}

func handleWebSocket(w http.ResponseWriter, r *http.Request) {
	conn, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		log.Println("Error upgrade websocket:", err)
		return
	}
	defer conn.Close()

	log.Println("Client terhubung ke System Monitor WebSocket!")

	for {
		// 1. CPU Usage
		cpuPercent, _ := cpu.Percent(time.Second, false)
		var cpuVal float64
		if len(cpuPercent) > 0 {
			cpuVal = cpuPercent[0]
		}

		// 2. RAM
		vMem, _ := mem.VirtualMemory()

		// 3. Host Info & Process Count
		hInfo, _ := host.Info()
		procs, _ := process.Pids()

		stats := SystemStats{
			CPUUsage:   cpuVal,
			CPUTemp:    getCPUTemp(),
			RAMUsage:   vMem.UsedPercent,
			RAMUsedMB:  vMem.Used / 1024 / 1024,
			RAMTotalMB: vMem.Total / 1024 / 1024,
			TotalProcs: len(procs),
			Battery:    getBatteryCapacity(),
			Uptime:     formatUptime(hInfo.Uptime),
			HostName:   hInfo.Hostname,
			Platform:   hInfo.Platform,
		}

		dataJson, err := json.Marshal(stats)
		if err != nil {
			log.Println("Error marshal json:", err)
			break
		}

		err = conn.WriteMessage(websocket.TextMessage, dataJson)
		if err != nil {
			log.Println("Client disconnect")
			break
		}

		time.Sleep(1 * time.Second)
	}
}

func main() {
	http.HandleFunc("/ws/system", handleWebSocket)
	port := ":8081"
	fmt.Println("🚀 Go System Monitor Service running di port", port)
	log.Fatal(http.ListenAndServe(port, nil))
}
