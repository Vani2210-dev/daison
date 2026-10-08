import React, { useState, useMemo, useEffect } from 'react';
import { Vehicle, VehicleAssignmentRecord } from '../types';
import { 
  ArrowLeft, 
  Truck, 
  UserCheck, 
  Users, 
  User, 
  Phone, 
  Calendar, 
  Clock, 
  MapPin, 
  Navigation,
  ShieldCheck, 
  Search, 
  Filter, 
  Plus, 
  CheckCircle2, 
  AlertCircle, 
  ChevronDown, 
  Trash2, 
  RotateCcw, 
  ClipboardList, 
  Check, 
  X,
  FileText,
  BadgeCheck
} from 'lucide-react';

// Format thousands helper
const formatThousands = (val: number | string | undefined | null): string => {
  if (val === undefined || val === null || val === '') return '';
  const numStr = String(val).replace(/[^\d]/g, '');
  if (numStr === '') return '';
  return Number(numStr).toLocaleString('vi-VN');
};

// Default delivery routes from system data
export const DEFAULT_DELIVERY_ROUTES = [
  { id: '1', code: 'TUYEN-01', name: 'Tuyáº¿n 01: Kho MĂª Linh - Giáº£i PhĂ³ng - TrÆ°á»ng Chinh - Nguyá»…n TrĂ£i', districts: 'Thanh XuĂ¢n, Giáº£i PhĂ³ng, TrÆ°á»ng Chinh, Nguyá»…n TrĂ£i, HĂ  ÄĂ´ng', distanceKm: 38, fuelAllowance: 250000, status: 'Hoáº¡t Ä‘á»™ng' },
  { id: '2', code: 'TUYEN-02', name: 'Tuyáº¿n 02: Kho ÄĂ´ng Anh - Cáº§u ThÄƒng Long - MĂª Linh - PhĂºc YĂªn', districts: 'ÄĂ´ng Anh, KCN ThÄƒng Long, MĂª Linh, PhĂºc YĂªn', distanceKm: 45, fuelAllowance: 300000, status: 'Hoáº¡t Ä‘á»™ng' },
  { id: '3', code: 'TUYEN-03', name: 'Tuyáº¿n 03: Cáº§u Giáº¥y - HoĂ ng Quá»‘c Viá»‡t - Báº¯c Tá»« LiĂªm - Nhá»•n - HoĂ i Äá»©c', districts: 'Cáº§u Giáº¥y, HoĂ ng Quá»‘c Viá»‡t, Báº¯c Tá»« LiĂªm, Nhá»•n, HoĂ i Äá»©c', distanceKm: 32, fuelAllowance: 200000, status: 'Hoáº¡t Ä‘á»™ng' },
  { id: '4', code: 'TUYEN-04', name: 'Tuyáº¿n 04: Ná»™i Ä‘Ă´ HĂ  Ná»™i (HoĂ n Kiáº¿m - Ba ÄĂ¬nh - Äá»‘ng Äa - Hai BĂ  TrÆ°ng)', districts: 'Ba ÄĂ¬nh, Äá»‘ng Äa, HoĂ n Kiáº¿m, Hai BĂ  TrÆ°ng', distanceKm: 26, fuelAllowance: 180000, status: 'Hoáº¡t Ä‘á»™ng' },
];

// Preset company drivers list
export const PRESET_DRIVERS = [
  { id: 'drv-01', name: 'VÅ© Quá»‘c Nam', phone: '0933 111 222', experience: '5 nÄƒm kinh nghiá»‡m', license: 'Báº±ng C', status: 'Sáºµn sĂ ng' },
  { id: 'drv-02', name: 'Nguyá»…n VÄƒn Äá»‹nh', phone: '0981 222 333', experience: '7 nÄƒm kinh nghiá»‡m', license: 'Báº±ng C', status: 'Sáºµn sĂ ng' },
  { id: 'drv-03', name: 'Tráº§n VÄƒn Máº¡nh', phone: '0915 444 555', experience: '4 nÄƒm kinh nghiá»‡m', license: 'Báº±ng C', status: 'Sáºµn sĂ ng' },
  { id: 'drv-04', name: 'Pháº¡m VÄƒn HĂ¹ng', phone: '0966 777 888', experience: '8 nÄƒm kinh nghiá»‡m', license: 'Báº±ng FC (Container)', status: 'Sáºµn sĂ ng' },
  { id: 'drv-05', name: 'BĂ¹i HoĂ ng Long', phone: '0944 333 777', experience: '6 nÄƒm kinh nghiá»‡m', license: 'Báº±ng C', status: 'Sáºµn sĂ ng' },
  { id: 'drv-06', name: 'BĂ¹i VÄƒn ThĂ nh', phone: '0966 334 455', experience: '3 nÄƒm kinh nghiá»‡m', license: 'Báº±ng B2', status: 'Sáºµn sĂ ng' },
  { id: 'drv-07', name: 'Nguyá»…n VÄƒn LĂ¡i', phone: '0977 123 456', experience: '5 nÄƒm kinh nghiá»‡m', license: 'Báº±ng C', status: 'Sáºµn sĂ ng' },
  { id: 'drv-08', name: 'Äá»— Minh TĂ¢n', phone: '0982 777 666', experience: '4 nÄƒm kinh nghiá»‡m', license: 'Báº±ng C', status: 'Sáºµn sĂ ng' },
];

// Preset company copilots / assistants list
export const PRESET_COPILOTS = [
  { id: 'cop-01', name: 'Nguyá»…n Tiáº¿n Äáº¡t', phone: '0978 222 333', role: 'Phá»¥ xe chuyĂªn tuyáº¿n', status: 'Sáºµn sĂ ng' },
  { id: 'cop-02', name: 'Tráº§n VÄƒn BĂ¬nh', phone: '0983 456 789', role: 'Phá»¥ xe chuyĂªn tuyáº¿n', status: 'Sáºµn sĂ ng' },
  { id: 'cop-03', name: 'LĂª VÄƒn KiĂªn', phone: '0912 345 678', role: 'Phá»¥ xe & bá»‘c dá»¡', status: 'Sáºµn sĂ ng' },
  { id: 'cop-04', name: 'HoĂ ng VÄƒn ThĂ¡i', phone: '0989 555 444', role: 'Phá»¥ xe Ä‘Æ°á»ng dĂ i', status: 'Sáºµn sĂ ng' },
  { id: 'cop-05', name: 'Nguyá»…n VÄƒn Huy', phone: '0904 111 888', role: 'Phá»¥ xe kho tá»•ng', status: 'Sáºµn sĂ ng' },
  { id: 'cop-06', name: 'VÅ© ÄĂ¬nh Trá»ng', phone: '0973 888 999', role: 'Phá»¥ xe & giao nháº­n', status: 'Sáºµn sĂ ng' },
];

// Preset vehicle managers
export const PRESET_MANAGERS = [
  { id: 'mgr-01', name: 'HoĂ ng VÄƒn Khoa', phone: '0988 222 111', title: 'TrÆ°á»Ÿng Ä‘á»™i xe & Äiá»u váº­n Logistics' },
  { id: 'mgr-02', name: 'LĂª HoĂ ng Khoa', phone: '0977 654 321', title: 'Quáº£n lĂ½ Kho Tá»•ng & Äá»™i xe' },
  { id: 'mgr-03', name: 'Nguyá»…n VÄƒn Äá»‹nh', phone: '0981 222 333', title: 'Phá»¥ trĂ¡ch Chi nhĂ¡nh VĂ¢n Quáº§n (MVQ)' },
  { id: 'mgr-04', name: 'Tráº§n VÄƒn Máº¡nh', phone: '0915 444 555', title: 'Phá»¥ trĂ¡ch Chi nhĂ¡nh ÄĂ´ng Anh (DXH)' },
];

// Initial mock assignments history
const DEFAULT_ASSIGNMENTS: VehicleAssignmentRecord[] = [
  {
    id: 'PC-2026-081',
    vehicleId: '1',
    vehiclePlate: '29C-888.99',
    driverName: 'BĂ¹i VÄƒn ThĂ nh',
    driverPhone: '0966 334 455',
    copilotName: 'Nguyá»…n Tiáº¿n Äáº¡t',
    copilotPhone: '0978 222 333',
    managerName: 'BĂ¹i VÄƒn ThĂ nh',
    managerPhone: '0966 334 455',
    assignedDate: '2026-09-01',
    effectiveFrom: '2026-09-01',
    effectiveTo: 'KhĂ´ng thá»i háº¡n',
    assignedBy: 'HoĂ ng VÄƒn Khoa (TrÆ°á»Ÿng Ä‘á»™i xe)',
    shift: 'ToĂ n thá»i gian (07:30 - 17:30)',
    routeId: '7',
    routeZone: 'Tuyáº¿n 01: TP. Vinh - HÆ°ng NguyĂªn - Nam ÄĂ n',
    status: 'Äang Ă¡p dá»¥ng',
    notes: 'BĂ n giao chĂ¬a khĂ³a xe, tháº» Ä‘á»• dáº§u Petrolimex sá»‘ 08 vĂ  bá»™ dĂ¢y tÄƒng Ä‘Æ¡ rĂ ng á»‘ng PVC.'
  },
  {
    id: 'PC-2026-082',
    vehicleId: '2',
    vehiclePlate: '29C-777.66',
    driverName: 'BĂ¹i VÄƒn ThĂ nh',
    driverPhone: '0966 334 455',
    copilotName: 'Tráº§n VÄƒn BĂ¬nh',
    copilotPhone: '0983 456 789',
    managerName: 'BĂ¹i VÄƒn ThĂ nh',
    managerPhone: '0966 334 455',
    assignedDate: '2026-09-01',
    effectiveFrom: '2026-09-01',
    effectiveTo: 'KhĂ´ng thá»i háº¡n',
    assignedBy: 'HoĂ ng VÄƒn Khoa',
    shift: 'ToĂ n thá»i gian',
    routeId: '8',
    routeZone: 'Tuyáº¿n 02: Báº¯c HĂ  TÄ©nh (Nghi XuĂ¢n - Há»“ng LÄ©nh - Can Lá»™c)',
    status: 'Äang Ă¡p dá»¥ng',
    notes: 'Xe thĂ¹ng dĂ i 6.2m chuyĂªn phá»¥ trĂ¡ch chá»Ÿ á»‘ng dĂ i 4m nguyĂªn cĂ¢y vĂ  phá»¥ kiá»‡n lá»›n.'
  },
  {
    id: 'PC-2026-083',
    vehicleId: '3',
    vehiclePlate: '29H-555.44',
    driverName: 'Tráº§n VÄƒn KiĂªn',
    driverPhone: '0912 345 678',
    copilotName: 'LĂª VÄƒn KiĂªn',
    copilotPhone: '0912 345 678',
    managerName: 'Tráº§n VÄƒn KiĂªn',
    managerPhone: '0912 345 678',
    assignedDate: '2026-09-01',
    effectiveFrom: '2026-09-01',
    effectiveTo: 'KhĂ´ng thá»i háº¡n',
    assignedBy: 'LĂª HoĂ ng Khoa',
    shift: 'ToĂ n thá»i gian',
    routeId: '9',
    routeZone: 'Tuyáº¿n 03: ÄĂ´ng Báº¯c Nghá»‡ An (Nghi Lá»™c - Diá»…n ChĂ¢u - Quá»³nh LÆ°u)',
    status: 'Äang Ă¡p dá»¥ng',
    notes: 'PhĂ¢n cĂ´ng chuyĂªn trĂ¡ch xe táº£i 8T chi viá»‡n tuyáº¿n ÄĂ´ng Báº¯c Nghá»‡ An.'
  },
  {
    id: 'PC-2026-084',
    vehicleId: '4',
    vehiclePlate: '29H-333.22',
    driverName: 'Nguyá»…n Tiáº¿n Äáº¡t',
    driverPhone: '0978 222 333',
    copilotName: 'HoĂ ng VÄƒn ThĂ¡i',
    copilotPhone: '0989 555 444',
    managerName: 'Nguyá»…n Tiáº¿n Äáº¡t',
    managerPhone: '0978 222 333',
    assignedDate: '2026-08-15',
    effectiveFrom: '2026-08-15',
    effectiveTo: 'KhĂ´ng thá»i háº¡n',
    assignedBy: 'HoĂ ng VÄƒn Khoa',
    shift: 'ToĂ n thá»i gian',
    routeId: '10',
    routeZone: 'Tuyáº¿n 04: Nam HĂ  TÄ©nh (Tháº¡ch HĂ  - TP. HĂ  TÄ©nh - Cáº©m XuyĂªn - Ká»³ Anh)',
    status: 'Äang Ă¡p dá»¥ng',
    notes: 'Xe Ford Ranger phá»¥ trĂ¡ch tuyáº¿n Nam HĂ  TÄ©nh.'
  }
];

interface VehicleAssignmentViewProps {
  vehicle: any;
  allVehicles: any[];
  deliveryRoutes?: any[];
  onBack: () => void;
  onUpdateVehicle: (updated: any) => void;
  onSelectVehicle?: (veh: any) => void;
}

export const VehicleAssignmentView: React.FC<VehicleAssignmentViewProps> = ({
  vehicle,
  allVehicles = [],
  deliveryRoutes = [],
  onBack,
  onUpdateVehicle,
  onSelectVehicle
}) => {
  const [currentVehicle, setCurrentVehicle] = useState<any>(vehicle);

  // Sync if vehicle prop changes
  useEffect(() => {
    if (vehicle && vehicle.id !== currentVehicle.id) {
      setCurrentVehicle(vehicle);
    }
  }, [vehicle]);

  // Assignment history state loaded from database, localStorage or default
  const [assignments, setAssignments] = useState<VehicleAssignmentRecord[]>(() => {
    if (typeof window !== 'undefined') {
      const erpAssignments = (window as any).__ERP_DATA__?.vehicleAssignments;
      if (Array.isArray(erpAssignments) && erpAssignments.length > 0) {
        return erpAssignments;
      }
      const saved = localStorage.getItem('erp_vehicle_assignments');
      if (saved) {
        try {
          const parsed = JSON.parse(saved);
          if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        } catch (e) {
          console.error('Failed to parse vehicle assignments', e);
        }
      }
    }
    return DEFAULT_ASSIGNMENTS;
  });

  // Persist assignments to localStorage
  useEffect(() => {
    if (typeof window !== 'undefined') {
      localStorage.setItem('erp_vehicle_assignments', JSON.stringify(assignments));
    }
  }, [assignments]);

  // Filter state for assignments history
  const [historySearchTerm, setHistorySearchTerm] = useState('');
  const [historyStatusFilter, setHistoryStatusFilter] = useState<'all' | 'Äang Ă¡p dá»¥ng' | 'HoĂ n thĂ nh' | 'Táº¡m ngÆ°ng'>('all');
  const [viewOnlyCurrentVehicle, setViewOnlyCurrentVehicle] = useState(true);

  // Resolve available routes from props, localStorage, or defaults
  const availableRoutes = useMemo(() => {
    if (deliveryRoutes && deliveryRoutes.length > 0) {
      return deliveryRoutes;
    }
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('erp_delivery_routes_list');
      if (saved) {
        try {
          const parsed = JSON.parse(saved);
          if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        } catch (e) {}
      }
    }
    return DEFAULT_DELIVERY_ROUTES;
  }, [deliveryRoutes]);

  // Form states for new assignment
  const [driverName, setDriverName] = useState(currentVehicle.driverName || 'VÅ© Quá»‘c Nam');
  const [driverPhone, setDriverPhone] = useState(currentVehicle.driverPhone || '0933 111 222');
  const [copilotName, setCopilotName] = useState(currentVehicle.copilotName || 'Nguyá»…n Tiáº¿n Äáº¡t');
  const [copilotPhone, setCopilotPhone] = useState(currentVehicle.copilotPhone || '0978 222 333');
  const [managerName, setManagerName] = useState(currentVehicle.managerName || 'HoĂ ng VÄƒn Khoa');
  const [managerPhone, setManagerPhone] = useState(currentVehicle.managerPhone || '0988 222 111');
  
  const [effectiveFrom, setEffectiveFrom] = useState(() => new Date().toISOString().split('T')[0]);
  const [isIndefinite, setIsIndefinite] = useState(true);
  const [effectiveTo, setEffectiveTo] = useState('');
  const [shift, setShift] = useState('ToĂ n thá»i gian (07:30 - 17:30)');
  const [selectedRouteId, setSelectedRouteId] = useState<string>('');
  const [routeZone, setRouteZone] = useState<string>('');
  const [assignedBy, setAssignedBy] = useState('HoĂ ng VÄƒn Khoa (TrÆ°á»Ÿng Ä‘á»™i xe)');
  const [notes, setNotes] = useState('');

  // Toast notification
  const [toastMsg, setToastMsg] = useState<string | null>(null);
  const showToast = (msg: string) => {
    setToastMsg(msg);
    setTimeout(() => setToastMsg(null), 3500);
  };

  // When currentVehicle or availableRoutes change, sync form inputs and assigned route
  useEffect(() => {
    setDriverName(currentVehicle.driverName || '');
    setDriverPhone(currentVehicle.driverPhone || '');
    setCopilotName(currentVehicle.copilotName || '');
    setCopilotPhone(currentVehicle.copilotPhone || '');
    setManagerName(currentVehicle.managerName || 'HoĂ ng VÄƒn Khoa');
    setManagerPhone(currentVehicle.managerPhone || '0988 222 111');

    // Match route from system data
    let matchedRoute: any = null;
    if (currentVehicle.deliveryRouteId) {
      matchedRoute = availableRoutes.find(r => String(r.id) === String(currentVehicle.deliveryRouteId));
    }
    if (!matchedRoute && currentVehicle.deliveryRouteName) {
      matchedRoute = availableRoutes.find(r => r.name === currentVehicle.deliveryRouteName);
    }
    if (!matchedRoute) {
      const activeRec = assignments.find(a => a.vehiclePlate === currentVehicle.plateNumber && a.status === 'Äang Ă¡p dá»¥ng');
      if (activeRec) {
        if (activeRec.routeId) {
          matchedRoute = availableRoutes.find(r => String(r.id) === String(activeRec.routeId));
        }
        if (!matchedRoute && activeRec.routeZone) {
          matchedRoute = availableRoutes.find(r => r.name === activeRec.routeZone || activeRec.routeZone.includes(r.name) || r.name.includes(activeRec.routeZone));
        }
      }
    }

    if (matchedRoute) {
      setSelectedRouteId(String(matchedRoute.id));
      setRouteZone(matchedRoute.name);
    } else {
      setSelectedRouteId('');
      setRouteZone('');
    }
  }, [currentVehicle, availableRoutes]);

  // Selected route object from data
  const selectedRouteObj = useMemo(() => {
    if (!selectedRouteId) return null;
    return availableRoutes.find(r => String(r.id) === String(selectedRouteId)) || null;
  }, [selectedRouteId, availableRoutes]);

  // Check if selected route is currently assigned to another vehicle
  const conflictVehicle = useMemo(() => {
    if (!selectedRouteId) return null;
    return allVehicles.find(v => 
      String(v.id) !== String(currentVehicle.id) && 
      String(v.deliveryRouteId) === String(selectedRouteId)
    ) || null;
  }, [selectedRouteId, allVehicles, currentVehicle]);

  // Currently assigned route for current vehicle
  const currentAssignedRoute = useMemo(() => {
    if (currentVehicle.deliveryRouteId) {
      const found = availableRoutes.find(r => String(r.id) === String(currentVehicle.deliveryRouteId));
      if (found) return found;
    }
    if (currentVehicle.deliveryRouteName) {
      const found = availableRoutes.find(r => r.name === currentVehicle.deliveryRouteName);
      if (found) return found;
    }
    const activeAss = assignments.find(a => a.vehiclePlate === currentVehicle.plateNumber && a.status === 'Äang Ă¡p dá»¥ng');
    if (activeAss && activeAss.routeZone) {
      const found = availableRoutes.find(r => r.name === activeAss.routeZone);
      if (found) return found;
      return { id: 'custom', name: activeAss.routeZone, districts: '' };
    }
    return null;
  }, [currentVehicle, availableRoutes, assignments]);

  // Handle route selection from dropdown
  const handleSelectRoute = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const routeId = e.target.value;
    setSelectedRouteId(routeId);
    if (!routeId) {
      setRouteZone('');
      return;
    }
    const found = availableRoutes.find(r => String(r.id) === String(routeId));
    if (found) {
      setRouteZone(found.name);
    }
  };

  // Handle preset driver selection
  const handleSelectDriver = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = e.target.value;
    if (val === 'CUSTOM') return;
    const found = PRESET_DRIVERS.find(d => d.name === val);
    if (found) {
      setDriverName(found.name);
      setDriverPhone(found.phone);
    }
  };

  // Handle preset copilot selection
  const handleSelectCopilot = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = e.target.value;
    if (val === 'CUSTOM') return;
    const found = PRESET_COPILOTS.find(c => c.name === val);
    if (found) {
      setCopilotName(found.name);
      setCopilotPhone(found.phone);
    }
  };

  // Handle preset manager selection
  const handleSelectManager = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = e.target.value;
    const found = PRESET_MANAGERS.find(m => m.name === val);
    if (found) {
      setManagerName(found.name);
      setManagerPhone(found.phone);
    }
  };

  // Submit new assignment
  const handleSaveAssignment = (e: React.FormEvent) => {
    e.preventDefault();

    if (!driverName.trim()) {
      alert('Vui lĂ²ng chá»n hoáº·c nháº­p tĂªn LĂ¡i xe chĂ­nh!');
      return;
    }

    const newRecordId = `PC-${new Date().getFullYear()}-${String(Math.floor(100 + Math.random() * 900))}`;
    const todayStr = new Date().toISOString().split('T')[0];
    const finalRouteName = selectedRouteObj ? selectedRouteObj.name : (routeZone.trim() || 'Tuyáº¿n tá»± do / Ná»™i bá»™');

    const newAssignment: VehicleAssignmentRecord = {
      id: newRecordId,
      vehicleId: currentVehicle.id,
      vehiclePlate: currentVehicle.plateNumber,
      driverName: driverName.trim(),
      driverPhone: driverPhone.trim(),
      copilotName: copilotName.trim() || 'KhĂ´ng cĂ³ phá»¥ xe',
      copilotPhone: copilotPhone.trim(),
      managerName: managerName.trim() || 'HoĂ ng VÄƒn Khoa',
      managerPhone: managerPhone.trim() || '0988 222 111',
      assignedDate: todayStr,
      effectiveFrom: effectiveFrom || todayStr,
      effectiveTo: isIndefinite ? 'KhĂ´ng thá»i háº¡n' : (effectiveTo || 'KhĂ´ng thá»i háº¡n'),
      assignedBy: assignedBy.trim() || 'Quáº£n lĂ½ Ä‘á»™i xe',
      shift,
      routeId: selectedRouteId || undefined,
      routeZone: finalRouteName,
      status: 'Äang Ă¡p dá»¥ng',
      notes: notes.trim()
    };

    // Mark previous "Äang Ă¡p dá»¥ng" assignments of this vehicle as "HoĂ n thĂ nh"
    const updatedAssignments = assignments.map(a => {
      if (a.vehiclePlate === currentVehicle.plateNumber && a.status === 'Äang Ă¡p dá»¥ng') {
        return {
          ...a,
          status: 'HoĂ n thĂ nh' as const,
          effectiveTo: todayStr
        };
      }
      return a;
    });

    const finalAssignmentsList = [newAssignment, ...updatedAssignments];
    setAssignments(finalAssignmentsList);

    // Update the vehicle state including assigned delivery route
    const updatedVehicleData = {
      ...currentVehicle,
      driverName: driverName.trim(),
      driverPhone: driverPhone.trim(),
      copilotName: copilotName.trim() || 'KhĂ´ng cĂ³ phá»¥ xe',
      copilotPhone: copilotPhone.trim(),
      managerName: managerName.trim(),
      managerPhone: managerPhone.trim(),
      deliveryRouteId: selectedRouteId || null,
      deliveryRouteName: selectedRouteObj ? selectedRouteObj.name : null,
    };

    setCurrentVehicle(updatedVehicleData);
    onUpdateVehicle(updatedVehicleData);

    // Persist to database via Laravel API
    fetch('/api/vehicle-assignments/save', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(newAssignment)
    }).catch(err => console.error('Failed to save assignment to database', err));

    showToast(`ÄĂ£ lÆ°u & Ă¡p dá»¥ng phĂ¢n cĂ´ng tuyáº¿n ${finalRouteName} cho xe ${currentVehicle.plateNumber}!`);
  };

  // Handle quick terminate / end an assignment
  const handleTerminateAssignment = (recordId: string) => {
    if (!confirm('Báº¡n cĂ³ cháº¯c cháº¯n muá»‘n káº¿t thĂºc Ä‘á»£t phĂ¢n cĂ´ng nĂ y khĂ´ng?')) return;

    setAssignments(prev => prev.map(a => {
      if (a.id === recordId) {
        return {
          ...a,
          status: 'HoĂ n thĂ nh',
          effectiveTo: new Date().toISOString().split('T')[0]
        };
      }
      return a;
    }));

    showToast('ÄĂ£ káº¿t thĂºc Ä‘á»£t phĂ¢n cĂ´ng.');
  };

  // Handle delete an assignment record
  const handleDeleteAssignment = (recordId: string) => {
    if (!confirm('Báº¡n cĂ³ cháº¯c cháº¯n muá»‘n xĂ³a báº£n ghi lá»‹ch sá»­ phĂ¢n cĂ´ng nĂ y khĂ´ng?')) return;
    setAssignments(prev => prev.filter(a => a.id !== recordId));
    showToast('ÄĂ£ xĂ³a báº£n ghi phĂ¢n cĂ´ng khá»i lá»‹ch sá»­.');
  };

  // Filtered history list
  const filteredHistory = useMemo(() => {
    return assignments.filter(item => {
      // Filter by vehicle
      if (viewOnlyCurrentVehicle && item.vehiclePlate !== currentVehicle.plateNumber) {
        return false;
      }

      // Filter by status
      if (historyStatusFilter !== 'all' && item.status !== historyStatusFilter) {
        return false;
      }

      // Search term
      if (historySearchTerm) {
        const q = historySearchTerm.toLowerCase();
        const str = `${item.id} ${item.vehiclePlate} ${item.driverName} ${item.copilotName} ${item.managerName} ${item.routeZone} ${item.notes}`.toLowerCase();
        return str.includes(q);
      }

      return true;
    });
  }, [assignments, viewOnlyCurrentVehicle, currentVehicle.plateNumber, historyStatusFilter, historySearchTerm]);

  // Current active assignment for this vehicle
  const currentActiveAssignment = useMemo(() => {
    return assignments.find(a => a.vehiclePlate === currentVehicle.plateNumber && a.status === 'Äang Ă¡p dá»¥ng');
  }, [assignments, currentVehicle.plateNumber]);

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      
      {/* Toast Notification */}
      {toastMsg && (
        <div className="fixed top-20 right-6 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center space-x-2 border border-slate-700 animate-in slide-in-from-top-2">
          <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0" />
          <span className="text-xs sm:text-sm font-semibold">{toastMsg}</span>
        </div>
      )}

      {/* Top Navigation & Breadcrumb */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div className="flex items-center space-x-3">
          <button
            type="button"
            onClick={onBack}
            className="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all flex items-center gap-1.5 font-bold text-xs cursor-pointer active:scale-95 shrink-0"
            title="Quay láº¡i danh má»¥c xe giao hĂ ng"
          >
            <ArrowLeft className="w-4 h-4 text-slate-600" />
            <span className="hidden sm:inline">Quay Láº¡i Danh Má»¥c</span>
          </button>

          <div className="h-6 w-px bg-slate-200" />

          <div>
            <div className="flex items-center space-x-2 text-[11px] text-slate-500 font-medium">
              <span>Danh má»¥c</span>
              <span>/</span>
              <span>Xe giao hĂ ng</span>
              <span>/</span>
              <span className="text-indigo-600 font-bold">PhĂ¢n cĂ´ng xe</span>
            </div>
            <h1 className="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
              <UserCheck className="w-5 h-5 text-indigo-600" />
              <span>PhĂ¢n CĂ´ng LĂ¡i Xe & Phá»¥ Xe Cho Äá»™i Xe Váº­n Táº£i</span>
            </h1>
          </div>
        </div>

        {/* Quick Vehicle Switcher Dropdown */}
        <div className="flex items-center space-x-2 w-full md:w-auto justify-end">
          <span className="text-xs text-slate-500 font-medium shrink-0">Äang chá»n xe:</span>
          <div className="relative">
            <select
              value={currentVehicle.id}
              onChange={(e) => {
                const target = allVehicles.find(v => v.id === e.target.value);
                if (target) {
                  setCurrentVehicle(target);
                  if (onSelectVehicle) onSelectVehicle(target);
                }
              }}
              className="pl-3 pr-8 py-2 bg-indigo-50/80 border border-indigo-200 rounded-xl text-xs font-mono font-extrabold text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 cursor-pointer appearance-none"
            >
              {allVehicles.map((v) => (
                <option key={v.id} value={v.id}>
                  {v.plateNumber} - {v.vehicleType}
                </option>
              ))}
            </select>
            <ChevronDown className="w-3.5 h-3.5 absolute right-2.5 top-3 text-indigo-600 pointer-events-none" />
          </div>
        </div>
      </div>

      {/* Vehicle Summary Banner Card */}
      <div className="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 shadow-lg border border-slate-800">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
          
          {/* Col 1: Plate & Vehicle details */}
          <div className="flex items-start space-x-3.5 sm:border-r border-slate-800/80 sm:pr-4">
            <div className="px-3 py-2 bg-amber-300 text-slate-950 font-black font-mono text-lg rounded-xl border-2 border-slate-900 shadow-md tracking-wider flex items-center gap-1.5 shrink-0">
              <Truck className="w-5 h-5 text-slate-900" />
              <span>{currentVehicle.plateNumber}</span>
            </div>
            <div>
              <h3 className="font-extrabold text-sm text-white">{currentVehicle.vehicleType}</h3>
              <p className="text-xs text-slate-400 mt-0.5">Táº£i trá»ng: <span className="text-white font-mono font-bold">{(currentVehicle.capacityKg || 2500).toLocaleString('vi-VN')} kg</span></p>
              <div className="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
                <span>{currentVehicle.status || 'Hoáº¡t Ä‘á»™ng'}</span>
              </div>
            </div>
          </div>

          {/* Col 2: Current Assigned Route */}
          <div className="space-y-1 sm:border-r border-slate-800/80 sm:pr-4">
            <div className="flex items-center space-x-1.5 text-xs text-slate-400">
              <Navigation className="w-3.5 h-3.5 text-emerald-400" />
              <span className="font-semibold uppercase text-[10px] tracking-wider text-emerald-300">Tuyáº¿n GH Phá»¥ TrĂ¡ch</span>
            </div>
            {currentAssignedRoute ? (
              <div className="space-y-0.5">
                <p className="text-xs font-extrabold text-emerald-300 leading-snug line-clamp-2" title={currentAssignedRoute.name}>
                  {currentAssignedRoute.name}
                </p>
                {currentAssignedRoute.districts && (
                  <p className="text-[10.5px] text-slate-300 line-clamp-1" title={currentAssignedRoute.districts}>
                    {currentAssignedRoute.districts}
                  </p>
                )}
                {currentAssignedRoute.distanceKm && (
                  <p className="text-[10px] font-mono text-emerald-400/90 font-bold">
                    Cá»± ly: {currentAssignedRoute.distanceKm} km
                  </p>
                )}
              </div>
            ) : (
              <div className="space-y-0.5">
                <p className="text-xs font-bold text-slate-400 italic">-- ChÆ°a gĂ¡n tuyáº¿n --</p>
                <p className="text-[10px] text-slate-500">Chá»n tuyáº¿n cá»‘ Ä‘á»‹nh á»Ÿ form bĂªn dÆ°á»›i</p>
              </div>
            )}
          </div>

          {/* Col 3: Current Person in Charge (NgÆ°á»i phá»¥ trĂ¡ch) */}
          <div className="space-y-1 lg:border-r border-slate-800/80 lg:pr-4">
            <div className="flex items-center space-x-1.5 text-xs text-slate-400">
              <ShieldCheck className="w-3.5 h-3.5 text-sky-400" />
              <span className="font-semibold uppercase text-[10px] tracking-wider text-sky-300">NgÆ°á»i Phá»¥ TrĂ¡ch Quáº£n LĂ½</span>
            </div>
            <p className="text-sm font-extrabold text-white">{currentVehicle.managerName || managerName || 'HoĂ ng VÄƒn Khoa'}</p>
            <p className="text-xs font-mono text-slate-300 flex items-center gap-1">
              <Phone className="w-3 h-3 text-slate-400" />
              <span>{currentVehicle.managerPhone || managerPhone || '0988 222 111'}</span>
            </p>
            <p className="text-[11px] text-slate-400 italic">Chá»‹u trĂ¡ch nhiá»‡m báº£o quáº£n & Ä‘iá»u Ä‘á»™ng</p>
          </div>

          {/* Col 4: Current Driver */}
          <div className="space-y-1 sm:border-r border-slate-800/80 sm:pr-4">
            <div className="flex items-center space-x-1.5 text-xs text-slate-400">
              <User className="w-3.5 h-3.5 text-indigo-400" />
              <span className="font-semibold uppercase text-[10px] tracking-wider text-indigo-300">LĂ¡i Xe ChĂ­nh Hiá»‡n Táº¡i</span>
            </div>
            <p className="text-sm font-extrabold text-white">{currentVehicle.driverName || 'ChÆ°a phĂ¢n cĂ´ng'}</p>
            <p className="text-xs font-mono text-slate-300 flex items-center gap-1">
              <Phone className="w-3 h-3 text-slate-400" />
              <span>{currentVehicle.driverPhone || 'N/A'}</span>
            </p>
            <p className="text-[11px] text-slate-400">
              {currentActiveAssignment ? `Báº¯t Ä‘áº§u: ${currentActiveAssignment.effectiveFrom}` : 'Phá»¥ trĂ¡ch xe giao hĂ ng'}
            </p>
          </div>

          {/* Col 5: Current Copilot */}
          <div className="space-y-1">
            <div className="flex items-center space-x-1.5 text-xs text-slate-400">
              <Users className="w-3.5 h-3.5 text-purple-400" />
              <span className="font-semibold uppercase text-[10px] tracking-wider text-purple-300">Phá»¥ Xe Hiá»‡n Táº¡i</span>
            </div>
            <p className="text-sm font-extrabold text-white">{currentVehicle.copilotName || 'KhĂ´ng cĂ³ phá»¥ xe'}</p>
            <p className="text-xs font-mono text-slate-300 flex items-center gap-1">
              <Phone className="w-3 h-3 text-slate-400" />
              <span>{currentVehicle.copilotPhone || 'N/A'}</span>
            </p>
            <p className="text-[11px] text-slate-400">Há»— trá»£ bá»‘c dá»¡ & kiá»ƒm Ä‘áº¿m á»‘ng PVC</p>
          </div>

        </div>
      </div>

      {/* Main Grid: Form on Left, Assignment History Table on Right */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {/* Left Column: Form Thiáº¿t Láº­p PhĂ¢n CĂ´ng Má»›i (5 cols) */}
        <div className="lg:col-span-5 space-y-4">
          <div className="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-4">
            
            <div className="flex items-center justify-between border-b border-slate-100 pb-3">
              <div className="flex items-center space-x-2">
                <div className="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                  <UserCheck className="w-4 h-4" />
                </div>
                <div>
                  <h2 className="font-extrabold text-slate-900 text-sm">Thiáº¿t Láº­p PhĂ¢n CĂ´ng Má»›i</h2>
                  <p className="text-xs text-slate-500">Cáº­p nháº­t tĂ i xáº¿, phá»¥ xe & ca lĂ m viá»‡c cho xe {currentVehicle.plateNumber}</p>
                </div>
              </div>
            </div>

            <form onSubmit={handleSaveAssignment} className="space-y-4 text-xs">
              
              {/* Select & Input Driver */}
              <div className="p-3.5 bg-indigo-50/50 border border-indigo-100 rounded-xl space-y-2.5">
                <div className="flex items-center justify-between">
                  <label className="font-extrabold text-indigo-950 flex items-center gap-1">
                    <User className="w-3.5 h-3.5 text-indigo-600" />
                    <span>1. LĂ¡i Xe ChĂ­nh (TĂ i Xáº¿) *</span>
                  </label>
                  <span className="text-[10px] text-indigo-600 font-bold bg-indigo-100/70 px-2 py-0.5 rounded">
                    Báº¯t buá»™c
                  </span>
                </div>

                <div>
                  <label className="block text-[11px] text-slate-500 font-medium mb-1">Chá»n tá»« danh sĂ¡ch tĂ i xáº¿ cĂ´ng ty:</label>
                  <select
                    value={PRESET_DRIVERS.some(d => d.name === driverName) ? driverName : 'CUSTOM'}
                    onChange={handleSelectDriver}
                    className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500"
                  >
                    <option value="CUSTOM">-- Nháº­p tĂªn tĂ i xáº¿ tĂ¹y chá»‰nh bĂªn dÆ°á»›i --</option>
                    {PRESET_DRIVERS.map(d => (
                      <option key={d.id} value={d.name}>
                        {d.name} ({d.phone}) - {d.license}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                  <div>
                    <label className="block text-[11px] text-slate-600 font-bold mb-1">Há» & TĂªn LĂ¡i Xe *</label>
                    <input
                      type="text"
                      required
                      placeholder="VD: VÅ© Quá»‘c Nam..."
                      value={driverName}
                      onChange={e => setDriverName(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] text-slate-600 font-bold mb-1">Sá»‘ Äiá»‡n Thoáº¡i</label>
                    <input
                      type="text"
                      placeholder="0933 111 222..."
                      value={driverPhone}
                      onChange={e => setDriverPhone(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500"
                    />
                  </div>
                </div>
              </div>

              {/* Select & Input Copilot */}
              <div className="p-3.5 bg-purple-50/50 border border-purple-100 rounded-xl space-y-2.5">
                <div className="flex items-center justify-between">
                  <label className="font-extrabold text-purple-950 flex items-center gap-1">
                    <Users className="w-3.5 h-3.5 text-purple-600" />
                    <span>2. Phá»¥ Xe (LĂ¡i Phá»¥ / Giao Nháº­n)</span>
                  </label>
                  <span className="text-[10px] text-purple-600 font-bold bg-purple-100/70 px-2 py-0.5 rounded">
                    TĂ¹y chá»n
                  </span>
                </div>

                <div>
                  <label className="block text-[11px] text-slate-500 font-medium mb-1">Chá»n tá»« danh sĂ¡ch phá»¥ xe:</label>
                  <select
                    value={PRESET_COPILOTS.some(c => c.name === copilotName) ? copilotName : 'CUSTOM'}
                    onChange={handleSelectCopilot}
                    className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500"
                  >
                    <option value="CUSTOM">-- Nháº­p tĂªn phá»¥ xe tĂ¹y chá»‰nh bĂªn dÆ°á»›i --</option>
                    {PRESET_COPILOTS.map(c => (
                      <option key={c.id} value={c.name}>
                        {c.name} ({c.phone}) - {c.role}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                  <div>
                    <label className="block text-[11px] text-slate-600 font-bold mb-1">Há» & TĂªn Phá»¥ Xe</label>
                    <input
                      type="text"
                      placeholder="VD: Nguyá»…n Tiáº¿n Äáº¡t..."
                      value={copilotName}
                      onChange={e => setCopilotName(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] text-slate-600 font-bold mb-1">Sá»‘ Äiá»‡n Thoáº¡i</label>
                    <input
                      type="text"
                      placeholder="0978 222 333..."
                      value={copilotPhone}
                      onChange={e => setCopilotPhone(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-medium text-slate-900 focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500"
                    />
                  </div>
                </div>
              </div>

              {/* Person in charge (NgÆ°á»i phá»¥ trĂ¡ch xe) */}
              <div className="p-3.5 bg-sky-50/50 border border-sky-100 rounded-xl space-y-2">
                <label className="font-extrabold text-sky-950 flex items-center gap-1">
                  <ShieldCheck className="w-3.5 h-3.5 text-sky-600" />
                  <span>3. NgÆ°á»i Phá»¥ TrĂ¡ch Quáº£n LĂ½ Xe</span>
                </label>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <div>
                    <select
                      value={PRESET_MANAGERS.some(m => m.name === managerName) ? managerName : 'CUSTOM'}
                      onChange={handleSelectManager}
                      className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500"
                    >
                      {PRESET_MANAGERS.map(m => (
                        <option key={m.id} value={m.name}>
                          {m.name} ({m.phone})
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <input
                      type="text"
                      placeholder="SÄT ngÆ°á»i phá»¥ trĂ¡ch..."
                      value={managerPhone}
                      onChange={e => setManagerPhone(e.target.value)}
                      className="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-medium text-slate-900"
                    />
                  </div>
                </div>
              </div>

              {/* Timing & Effective Date */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-slate-700 mb-1 flex items-center gap-1">
                    <Calendar className="w-3.5 h-3.5 text-slate-500" />
                    <span>Ăp Dá»¥ng Tá»« NgĂ y *</span>
                  </label>
                  <input
                    type="date"
                    required
                    value={effectiveFrom}
                    onChange={e => setEffectiveFrom(e.target.value)}
                    className="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/30"
                  />
                </div>

                <div>
                  <div className="flex items-center justify-between mb-1">
                    <label className="font-bold text-slate-700 flex items-center gap-1">
                      <Clock className="w-3.5 h-3.5 text-slate-500" />
                      <span>Äáº¿n NgĂ y</span>
                    </label>
                    <label className="flex items-center space-x-1 cursor-pointer select-none">
                      <input
                        type="checkbox"
                        checked={isIndefinite}
                        onChange={e => setIsIndefinite(e.target.checked)}
                        className="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                      />
                      <span className="text-[10.5px] text-indigo-700 font-bold">VĂ´ thá»i háº¡n</span>
                    </label>
                  </div>
                  <input
                    type="date"
                    disabled={isIndefinite}
                    value={effectiveTo}
                    onChange={e => setEffectiveTo(e.target.value)}
                    className={`w-full px-3 py-2 border rounded-xl text-xs font-medium ${
                      isIndefinite ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'border-slate-300 focus:ring-2 focus:ring-indigo-500/30'
                    }`}
                  />
                </div>
              </div>

              {/* 4. Tuyáº¿n Giao HĂ ng Phá»¥ TrĂ¡ch (Chá»n tá»« danh má»¥c Tuyáº¿n cĂ³ trong data, khĂ´ng nháº­p tay) */}
              <div className="p-3.5 bg-emerald-50/60 border border-emerald-200/80 rounded-xl space-y-2.5">
                <div className="flex items-center justify-between">
                  <label className="font-extrabold text-emerald-950 flex items-center gap-1.5">
                    <Navigation className="w-3.5 h-3.5 text-emerald-600" />
                    <span>4. Tuyáº¿n Giao HĂ ng Phá»¥ TrĂ¡ch *</span>
                  </label>
                  <span className="text-[10px] text-emerald-700 font-bold bg-emerald-100/90 px-2 py-0.5 rounded">
                    Chá»n tá»« dá»¯ liá»‡u tuyáº¿n
                  </span>
                </div>

                <div>
                  <label className="block text-[11px] text-slate-500 font-medium mb-1">
                    Chá»n tuyáº¿n giao hĂ ng phá»¥ trĂ¡ch cá»‘ Ä‘á»‹nh cho xe:
                  </label>
                  <select
                    value={selectedRouteId}
                    onChange={handleSelectRoute}
                    className="w-full px-3 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 cursor-pointer shadow-2xs"
                  >
                    <option value="">-- Chá»n tuyáº¿n giao hĂ ng tá»« dá»¯ liá»‡u há»‡ thá»‘ng --</option>
                    {availableRoutes.map(r => {
                      const otherVeh = allVehicles.find(v => 
                        String(v.id) !== String(currentVehicle.id) && 
                        String(v.deliveryRouteId) === String(r.id)
                      );
                      return (
                        <option key={r.id} value={r.id}>
                          {r.name} {otherVeh ? `(Hiá»‡n gĂ¡n xe ${otherVeh.plateNumber})` : ''}
                        </option>
                      );
                    })}
                  </select>
                </div>

                {/* Card hiá»ƒn thá»‹ chi tiáº¿t lá»™ trĂ¬nh tuyáº¿n Ä‘Ă£ chá»n */}
                {selectedRouteObj ? (
                  <div className="bg-white p-3 rounded-xl border border-emerald-200 text-xs space-y-1.5 shadow-2xs animate-fade-in">
                    <div className="flex items-center justify-between gap-2">
                      <div className="flex items-center gap-1.5 font-extrabold text-emerald-900 text-xs truncate">
                        <MapPin className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                        <span className="truncate">{selectedRouteObj.name}</span>
                      </div>
                      {selectedRouteObj.code && (
                        <span className="text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded shrink-0">
                          {selectedRouteObj.code}
                        </span>
                      )}
                    </div>
                    {selectedRouteObj.districts && (
                      <p className="text-[11px] text-slate-600 leading-snug">
                        <span className="font-bold text-slate-700">Khu vá»±c / Tráº¡m dá»«ng:</span> {selectedRouteObj.districts}
                      </p>
                    )}
                    <div className="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                      {selectedRouteObj.distanceKm && (
                        <span>Cá»± ly Æ°á»›c tĂ­nh: <strong className="text-slate-800">{selectedRouteObj.distanceKm} km</strong></span>
                      )}
                      {selectedRouteObj.fuelAllowance && (
                        <span>Äá»‹nh má»©c xÄƒng/dáº§u: <strong className="text-emerald-700">{formatThousands(selectedRouteObj.fuelAllowance)} Ä‘</strong></span>
                      )}
                    </div>
                    {conflictVehicle && (
                      <div className="p-2 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800 flex items-start gap-1.5 mt-1">
                        <AlertCircle className="w-3.5 h-3.5 text-amber-600 shrink-0 mt-0.5" />
                        <span>
                          Tuyáº¿n nĂ y hiá»‡n Ä‘ang gĂ¡n cho xe <strong className="font-mono text-amber-950">{conflictVehicle.plateNumber}</strong> ({conflictVehicle.driverName || 'LĂ¡i xe'}). Khi lÆ°u phĂ¢n cĂ´ng, tuyáº¿n sáº½ tá»± Ä‘á»™ng chuyá»ƒn sang xe <strong>{currentVehicle.plateNumber}</strong>.
                        </span>
                      </div>
                    )}
                  </div>
                ) : (
                  <p className="text-[11px] text-slate-400 italic">
                    Vui lĂ²ng chá»n 1 tuyáº¿n giao hĂ ng tá»« danh sĂ¡ch cĂ³ sáºµn phĂ­a trĂªn thay vĂ¬ nháº­p vÄƒn báº£n tá»± do.
                  </p>
                )}
              </div>

              {/* Ca LĂ m Viá»‡c */}
              <div className="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                <label className="block font-bold text-slate-700 text-xs">5. Ca LĂ m Viá»‡c</label>
                <select
                  value={shift}
                  onChange={e => setShift(e.target.value)}
                  className="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium bg-white focus:ring-2 focus:ring-indigo-500/30"
                >
                  <option value="ToĂ n thá»i gian (07:30 - 17:30)">ToĂ n thá»i gian (07:30 - 17:30)</option>
                  <option value="Ca ngĂ y (07:00 - 15:00)">Ca ngĂ y (07:00 - 15:00)</option>
                  <option value="Ca chiá»u (14:00 - 22:00)">Ca chiá»u (14:00 - 22:00)</option>
                  <option value="Theo chuyáº¿n liĂªn tá»‰nh">Theo chuyáº¿n liĂªn tá»‰nh</option>
                </select>
              </div>

              {/* Assigned By & Notes */}
              <div>
                <label className="block font-bold text-slate-700 mb-1">NgÆ°á»i Giao Viá»‡c / PhĂª Duyá»‡t</label>
                <input
                  type="text"
                  value={assignedBy}
                  onChange={e => setAssignedBy(e.target.value)}
                  placeholder="Há» tĂªn ngÆ°á»i phĂ¢n cĂ´ng..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium"
                />
              </div>

              <div>
                <label className="block font-bold text-slate-700 mb-1">Ghi ChĂº & BĂ n Giao Xe</label>
                <textarea
                  rows={2}
                  value={notes}
                  onChange={e => setNotes(e.target.value)}
                  placeholder="Ghi chĂº bĂ n giao chĂ¬a khĂ³a, dá»¥ng cá»¥ rĂ ng buá»™c hĂ ng, tĂ¬nh tráº¡ng xÄƒng dáº§u..."
                  className="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium"
                />
              </div>

              {/* Action Submit Button */}
              <div className="pt-2">
                <button
                  type="submit"
                  className="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2 cursor-pointer active:scale-98"
                >
                  <UserCheck className="w-4 h-4 stroke-[2.5]" />
                  <span>XĂ¡c Nháº­n & LÆ°u Ăp Dá»¥ng PhĂ¢n CĂ´ng</span>
                </button>
              </div>

            </form>

          </div>
        </div>

        {/* Right Column: Lá»‹ch Sá»­ PhĂ¢n CĂ´ng (Assignment History) (7 cols) */}
        <div className="lg:col-span-7 space-y-4">
          
          <div className="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col h-full">
            
            {/* History Header & Controls */}
            <div className="p-4 border-b border-slate-200 space-y-3 bg-slate-50/60">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div className="flex items-center space-x-2">
                  <div className="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <ClipboardList className="w-4 h-4" />
                  </div>
                  <div>
                    <h2 className="font-extrabold text-slate-900 text-sm">Lá»‹ch Sá»­ PhĂ¢n CĂ´ng LĂ¡i Xe & Phá»¥ Xe</h2>
                    <p className="text-xs text-slate-500">
                      Tá»•ng cá»™ng <strong className="text-indigo-600">{filteredHistory.length}</strong> Ä‘á»£t phĂ¢n cĂ´ng
                    </p>
                  </div>
                </div>

                {/* Filter toggle: only current vehicle vs all */}
                <div className="flex items-center space-x-2 self-start sm:self-auto">
                  <label className="flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold cursor-pointer shadow-2xs hover:bg-slate-50 select-none">
                    <input
                      type="checkbox"
                      checked={viewOnlyCurrentVehicle}
                      onChange={e => setViewOnlyCurrentVehicle(e.target.checked)}
                      className="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                    />
                    <span className="text-slate-700">Chá»‰ xe {currentVehicle.plateNumber}</span>
                  </label>
                </div>
              </div>

              {/* Filters Bar: Search & Status Pills */}
              <div className="flex flex-col sm:flex-row items-center justify-between gap-2 pt-1">
                <div className="relative w-full sm:w-64">
                  <Search className="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                  <input
                    type="text"
                    placeholder="TĂ¬m theo tĂ i xáº¿, phá»¥ xe, tuyáº¿n..."
                    value={historySearchTerm}
                    onChange={e => setHistorySearchTerm(e.target.value)}
                    className="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 font-medium"
                  />
                  {historySearchTerm && (
                    <button onClick={() => setHistorySearchTerm('')} className="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                <div className="flex items-center space-x-1 bg-slate-200/70 p-1 rounded-xl text-[11px] font-bold w-full sm:w-auto justify-center sm:justify-start">
                  <button
                    onClick={() => setHistoryStatusFilter('all')}
                    className={`px-2.5 py-1 rounded-lg transition-all ${historyStatusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                  >
                    Táº¥t cáº£
                  </button>
                  <button
                    onClick={() => setHistoryStatusFilter('Äang Ă¡p dá»¥ng')}
                    className={`px-2.5 py-1 rounded-lg transition-all ${historyStatusFilter === 'Äang Ă¡p dá»¥ng' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                  >
                    Äang Ă¡p dá»¥ng
                  </button>
                  <button
                    onClick={() => setHistoryStatusFilter('HoĂ n thĂ nh')}
                    className={`px-2.5 py-1 rounded-lg transition-all ${historyStatusFilter === 'HoĂ n thĂ nh' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                  >
                    HoĂ n thĂ nh
                  </button>
                </div>
              </div>

            </div>

            {/* History Table */}
            <div className="overflow-x-auto flex-1">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <th className="py-3 px-3 w-10 text-center">STT</th>
                    <th className="py-3 px-3">Xe & MĂ£</th>
                    <th className="py-3 px-3">LĂ¡i Xe ChĂ­nh</th>
                    <th className="py-3 px-3">Phá»¥ Xe</th>
                    <th className="py-3 px-3">Thá»i Gian</th>
                    <th className="py-3 px-3">Tuyáº¿n / Ca</th>
                    <th className="py-3 px-3 text-center">Tráº¡ng ThĂ¡i</th>
                    <th className="py-3 px-3 text-center w-20">Thao TĂ¡c</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 font-medium text-slate-700">
                  {filteredHistory.length === 0 ? (
                    <tr>
                      <td colSpan={8} className="py-12 text-center text-slate-400">
                        <div className="flex flex-col items-center justify-center space-y-2">
                          <ClipboardList className="w-8 h-8 text-slate-300 stroke-1" />
                          <p className="text-sm font-semibold">ChÆ°a cĂ³ báº£n ghi lá»‹ch sá»­ phĂ¢n cĂ´ng nĂ o phĂ¹ há»£p</p>
                          <p className="text-xs text-slate-400">Thiáº¿t láº­p phĂ¢n cĂ´ng má»›i á»Ÿ báº£ng bĂªn trĂ¡i Ä‘á»ƒ thĂªm báº£n ghi.</p>
                        </div>
                      </td>
                    </tr>
                  ) : (
                    filteredHistory.map((rec, idx) => (
                      <tr key={rec.id} className="hover:bg-slate-50/80 transition-colors">
                        
                        {/* STT */}
                        <td className="py-3 px-3 text-center text-slate-400 font-bold align-middle">
                          {idx + 1}
                        </td>

                        {/* Xe & MĂ£ */}
                        <td className="py-3 px-3 align-middle">
                          <div className="flex flex-col">
                            <span className="font-mono font-extrabold text-indigo-950 text-xs">
                              {rec.vehiclePlate}
                            </span>
                            <span className="font-mono text-[10px] text-slate-400">
                              {rec.id}
                            </span>
                          </div>
                        </td>

                        {/* LĂ¡i xe chĂ­nh */}
                        <td className="py-3 px-3 align-middle">
                          <div className="flex flex-col">
                            <span className="font-bold text-slate-900 text-xs flex items-center gap-1">
                              <User className="w-3 h-3 text-indigo-600" />
                              <span>{rec.driverName}</span>
                            </span>
                            {rec.driverPhone && (
                              <span className="text-[11px] font-mono text-slate-500">
                                {rec.driverPhone}
                              </span>
                            )}
                          </div>
                        </td>

                        {/* Phá»¥ xe */}
                        <td className="py-3 px-3 align-middle">
                          <div className="flex flex-col">
                            <span className="font-bold text-slate-800 text-xs flex items-center gap-1">
                              <Users className="w-3 h-3 text-purple-600" />
                              <span>{rec.copilotName}</span>
                            </span>
                            {rec.copilotPhone && (
                              <span className="text-[11px] font-mono text-slate-500">
                                {rec.copilotPhone}
                              </span>
                            )}
                          </div>
                        </td>

                        {/* Thá»i gian */}
                        <td className="py-3 px-3 align-middle text-xs">
                          <div className="flex flex-col">
                            <span className="text-slate-800 font-semibold">{rec.effectiveFrom}</span>
                            <span className="text-[11px] text-slate-400">Ä‘áº¿n {rec.effectiveTo || 'VĂ´ thá»i háº¡n'}</span>
                          </div>
                        </td>

                        {/* Tuyáº¿n / Ca */}
                        <td className="py-3 px-3 align-middle max-w-[190px]">
                          <div className="flex flex-col space-y-0.5">
                            <span className="text-slate-900 font-bold truncate flex items-center gap-1 text-xs" title={rec.routeZone}>
                              <Navigation className="w-3 h-3 text-emerald-600 shrink-0" />
                              <span className="truncate">{rec.routeZone || 'ChÆ°a gĂ¡n tuyáº¿n'}</span>
                            </span>
                            <span className="text-[10px] text-slate-500 truncate pl-4">
                              {rec.shift || 'ToĂ n thá»i gian'}
                            </span>
                          </div>
                        </td>

                        {/* Tráº¡ng thĂ¡i */}
                        <td className="py-3 px-3 text-center align-middle">
                          {rec.status === 'Äang Ă¡p dá»¥ng' ? (
                            <span className="inline-flex items-center space-x-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[10px]">
                              <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                              <span>Äang Ă¡p dá»¥ng</span>
                            </span>
                          ) : rec.status === 'HoĂ n thĂ nh' ? (
                            <span className="inline-flex items-center space-x-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full font-bold text-[10px]">
                              <span>HoĂ n thĂ nh</span>
                            </span>
                          ) : (
                            <span className="inline-flex items-center space-x-1 px-2 py-0.5 bg-slate-100 text-slate-600 border border-slate-300 rounded-full font-bold text-[10px]">
                              <span>{rec.status}</span>
                            </span>
                          )}
                        </td>

                        {/* Thao tĂ¡c */}
                        <td className="py-3 px-3 text-center align-middle">
                          <div className="flex items-center justify-center space-x-1">
                            {rec.status === 'Äang Ă¡p dá»¥ng' && (
                              <button
                                type="button"
                                onClick={() => handleTerminateAssignment(rec.id)}
                                className="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition-colors cursor-pointer"
                                title="Káº¿t thĂºc Ä‘á»£t phĂ¢n cĂ´ng nĂ y"
                              >
                                <RotateCcw className="w-3.5 h-3.5" />
                              </button>
                            )}
                            <button
                              type="button"
                              onClick={() => handleDeleteAssignment(rec.id)}
                              className="p-1.5 bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700 rounded-lg transition-colors cursor-pointer"
                              title="XĂ³a báº£n ghi khá»i lá»‹ch sá»­"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        </td>

                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Footer Summary */}
            <div className="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
              <span>* Táº¥t cáº£ cĂ¡c láº§n phĂ¢n cĂ´ng Ä‘Æ°á»£c lÆ°u váº¿t phá»¥c vá»¥ kiá»ƒm tra vĂ  tĂ­nh Ä‘á»‹nh má»©c cĂ´ng ná»£ giao hĂ ng.</span>
              <span className="font-bold text-slate-700">Xe: {currentVehicle.plateNumber}</span>
            </div>

          </div>

        </div>

      </div>

    </div>
  );
};
